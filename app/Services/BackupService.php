<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;
use RuntimeException;
use ZipArchive;

/**
 * Database / uploads / full backups that work on shared hosting.
 *
 *  - Database: `mysqldump` when the binary is callable, otherwise a pure-PHP
 *    dump (CREATE TABLE + batched INSERTs) that needs nothing but PDO.
 *  - Uploads: a zip of storage/uploads and storage/private (donor documents,
 *    CMS media). Logs and caches are never included.
 *  - Full: both in one archive.
 *
 * Output goes to config app.backups.path (default ../almarah-backups, outside
 * public_html), is gzip/zip compressed, optionally encrypted with
 * AES-256-CBC + HMAC using the configured passphrase, and recorded in
 * backup_logs with a SHA-256 checksum. Retention and an optional off-site
 * shell hook (rclone/scp) run after each successful backup.
 */
final class BackupService
{
    private const CIPHER = 'aes-256-cbc';

    public function __construct(
        private Database $db,
        private AuditService $audit,
        private string $basePath,
        private array $config,
        private array $dbConfig
    ) {
    }

    // ------------------------------------------------------------ queries

    public function path(): string
    {
        $path = trim((string) ($this->config['path'] ?? ''));
        if ($path === '') {
            $path = dirname($this->basePath) . '/almarah-backups';
        }
        return rtrim($path, '/');
    }

    public function encrypts(): bool
    {
        return trim((string) ($this->config['passphrase'] ?? '')) !== '';
    }

    public function keepDays(): int
    {
        return max(1, (int) ($this->config['keep_days'] ?? 30));
    }

    public function offsiteConfigured(): bool
    {
        return trim((string) ($this->config['offsite_cmd'] ?? '')) !== '';
    }

    /** @return array<string,mixed> readiness report for the admin page */
    public function status(): array
    {
        $dir = $this->path();
        $writable = is_dir($dir) ? is_writable($dir) : (is_writable(dirname($dir)) || @mkdir($dir, 0750, true));
        $insideWebRoot = str_starts_with(realpath($dir) ?: $dir, realpath($this->basePath) ?: $this->basePath);
        $last = $this->db->selectOne("SELECT * FROM backup_logs WHERE status = 'completed' ORDER BY id DESC LIMIT 1");
        $used = 0;
        if (is_dir($dir)) {
            foreach (glob($dir . '/*') ?: [] as $f) {
                $used += is_file($f) ? (int) filesize($f) : 0;
            }
        }
        return [
            'path'             => $dir,
            'writable'         => $writable,
            'inside_web_root'  => $insideWebRoot,
            'encrypted'        => $this->encrypts(),
            'keep_days'        => $this->keepDays(),
            'offsite'          => $this->offsiteConfigured(),
            'mysqldump'        => $this->mysqldumpBinary() !== null,
            'zip'              => class_exists(ZipArchive::class),
            'last_completed'   => $last,
            'last_age_hours'   => $last ? (int) round((time() - strtotime((string) $last['finished_at'] . ' UTC')) / 3600) : null,
            'disk_used_bytes'  => $used,
            'disk_free_bytes'  => is_dir($dir) ? (int) @disk_free_space($dir) : null,
        ];
    }

    /** @return array<int,array<string,mixed>> */
    public function logs(int $limit = 30): array
    {
        return $this->db->select(
            "SELECT b.*, CONCAT_WS(' ', u.first_name, u.last_name) AS triggered_by_name
             FROM backup_logs b LEFT JOIN users u ON u.id = b.triggered_by
             ORDER BY b.id DESC LIMIT {$limit}"
        );
    }

    public function find(int $id): ?array
    {
        return $this->db->selectOne('SELECT * FROM backup_logs WHERE id = :id', ['id' => $id]);
    }

    /** Absolute path of a logged backup file, or null if it is gone. */
    public function fileFor(array $log): ?string
    {
        $file = $this->path() . '/' . basename((string) $log['file_name']);
        return is_file($file) ? $file : null;
    }

    // --------------------------------------------------------------- runs

    /**
     * Create a backup. $kind: database | uploads | full.
     *
     * @return array{id:int,file:string,size:int,checksum:string}
     */
    public function run(string $kind, ?int $actorId = null, string $trigger = 'manual'): array
    {
        if (!in_array($kind, ['database', 'uploads', 'full'], true)) {
            throw new RuntimeException('Unknown backup kind.');
        }
        $dir = $this->path();
        if (!is_dir($dir) && !@mkdir($dir, 0750, true)) {
            throw new RuntimeException("Backup folder {$dir} cannot be created. Set backup_path in config.php to a writable folder outside public_html.");
        }
        $this->protectFolder($dir);

        $stamp = gmdate('Ymd-His');
        $ext = $kind === 'database' ? 'sql.gz' : 'zip';
        $fileName = "almarah-{$kind}-{$stamp}.{$ext}";
        $target = $dir . '/' . $fileName;

        $logId = $this->db->insert('backup_logs', [
            'kind'           => $kind,
            'file_name'      => $fileName,
            'encrypted'      => $this->encrypts() ? 1 : 0,
            'offsite_status' => $this->offsiteConfigured() ? 'pending' : 'not_configured',
            'status'         => 'running',
            'message'        => 'Started (' . $trigger . ')',
            'started_at'     => gmdate('Y-m-d H:i:s'),
            'triggered_by'   => $actorId,
        ]);

        try {
            @set_time_limit(0);
            if ($kind === 'database') {
                $this->dumpDatabaseGz($target);
            } else {
                $this->zipUploads($target, $kind === 'full');
            }

            if ($this->encrypts()) {
                $this->encryptFile($target);
                $fileName .= '.enc';
                $target .= '.enc';
            }

            $size = (int) filesize($target);
            $checksum = hash_file('sha256', $target) ?: '';

            $message = 'OK';
            $offsite = $this->offsiteConfigured() ? 'pending' : 'not_configured';
            if ($this->offsiteConfigured()) {
                [$offsite, $offMsg] = $this->pushOffsite($target);
                $message .= ' · off-site: ' . $offMsg;
            }

            $this->db->update('backup_logs', [
                'file_name'       => $fileName,
                'size_bytes'      => $size,
                'checksum_sha256' => $checksum,
                'offsite_status'  => $offsite,
                'status'          => 'completed',
                'message'         => $message,
                'finished_at'     => gmdate('Y-m-d H:i:s'),
            ], 'id = :id', ['id' => $logId]);

            $this->audit->log('backups.created', 'backup', $logId, ['kind' => $kind, 'size' => $size, 'trigger' => $trigger], $actorId);

            $this->applyRetention();

            return ['id' => $logId, 'file' => $target, 'size' => $size, 'checksum' => $checksum];
        } catch (\Throwable $e) {
            @unlink($target);
            @unlink($target . '.enc');
            $this->db->update('backup_logs', [
                'status'      => 'failed',
                'message'     => mb_substr($e->getMessage(), 0, 1000),
                'finished_at' => gmdate('Y-m-d H:i:s'),
            ], 'id = :id', ['id' => $logId]);
            $this->audit->log('backups.failed', 'backup', $logId, ['kind' => $kind, 'error' => $e->getMessage()], $actorId);
            throw $e;
        }
    }

    /** Delete a backup file (log row is kept, marked deleted). */
    public function delete(int $id, ?int $actorId = null): bool
    {
        $log = $this->find($id);
        if ($log === null) {
            return false;
        }
        $file = $this->fileFor($log);
        if ($file !== null) {
            @unlink($file);
        }
        $this->db->update('backup_logs', ['status' => 'deleted', 'message' => 'Deleted'], 'id = :id', ['id' => $id]);
        $this->audit->log('backups.deleted', 'backup', $id, ['file' => $log['file_name']], $actorId);
        return true;
    }

    /** Remove files older than keep_days (keeps at least the 3 newest of each kind). */
    public function applyRetention(): int
    {
        $cutoff = gmdate('Y-m-d H:i:s', time() - $this->keepDays() * 86400);
        $removed = 0;
        foreach (['database', 'uploads', 'full'] as $kind) {
            $rows = $this->db->select(
                "SELECT id, file_name FROM backup_logs WHERE kind = :k AND status = 'completed' ORDER BY id DESC",
                ['k' => $kind]
            );
            foreach (array_slice($rows, 3) as $row) {
                $full = $this->db->selectOne('SELECT finished_at FROM backup_logs WHERE id = :id', ['id' => (int) $row['id']]);
                if ($full !== null && (string) $full['finished_at'] < $cutoff) {
                    $file = $this->path() . '/' . basename((string) $row['file_name']);
                    @unlink($file);
                    $this->db->update('backup_logs', ['status' => 'deleted', 'message' => 'Removed by retention policy'], 'id = :id', ['id' => (int) $row['id']]);
                    $removed++;
                }
            }
        }
        return $removed;
    }

    // ----------------------------------------------------------- database

    private function dumpDatabaseGz(string $target): void
    {
        $bin = $this->mysqldumpBinary();
        if ($bin !== null && $this->tryMysqldump($bin, $target)) {
            return;
        }
        $this->phpDump($target);
    }

    private function mysqldumpBinary(): ?string
    {
        if (!function_exists('proc_open') || !function_exists('escapeshellarg')) {
            return null;
        }
        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));
        if (in_array('proc_open', $disabled, true) || in_array('exec', $disabled, true)) {
            return null;
        }
        foreach (['/usr/bin/mysqldump', '/usr/local/bin/mysqldump', '/opt/alt/mysql/bin/mysqldump', 'mysqldump'] as $candidate) {
            if (str_contains($candidate, '/') ? is_executable($candidate) : true) {
                $out = @shell_exec(escapeshellarg($candidate) . ' --version 2>/dev/null');
                if (is_string($out) && $out !== '' && stripos($out, 'mysqldump') !== false) {
                    return $candidate;
                }
            }
        }
        return null;
    }

    private function tryMysqldump(string $bin, string $target): bool
    {
        $cnf = tempnam(sys_get_temp_dir(), 'alm');
        if ($cnf === false) {
            return false;
        }
        // Credentials via a temporary defaults file so they never appear in `ps`.
        file_put_contents($cnf, "[client]\nhost=" . $this->dbConfig['host'] . "\nport=" . (int) ($this->dbConfig['port'] ?? 3306)
            . "\nuser=" . $this->dbConfig['username'] . "\npassword=\"" . str_replace('"', '\"', (string) $this->dbConfig['password']) . "\"\n");
        chmod($cnf, 0600);

        $raw = $target . '.tmp.sql';
        $cmd = escapeshellarg($bin) . ' --defaults-extra-file=' . escapeshellarg($cnf)
            . ' --single-transaction --quick --routines --triggers --default-character-set=utf8mb4 --no-tablespaces '
            . escapeshellarg((string) $this->dbConfig['database']) . ' > ' . escapeshellarg($raw) . ' 2>&1';

        $code = 1;
        @exec($cmd, $lines, $code);
        @unlink($cnf);

        if ($code !== 0 || !is_file($raw) || filesize($raw) < 100) {
            @unlink($raw);
            return false;
        }
        // gzip through PHP streams (no shell pipes — /bin/sh has no pipefail).
        $in = fopen($raw, 'rb');
        $out = gzopen($target, 'wb9');
        if ($in === false || $out === false) {
            @unlink($raw);
            return false;
        }
        while (!feof($in)) {
            gzwrite($out, (string) fread($in, 1 << 20));
        }
        fclose($in);
        gzclose($out);
        @unlink($raw);

        // Sanity: a gzip that decompresses to a mysqldump header.
        $head = (string) @file_get_contents('compress.zlib://' . $target, false, null, 0, 200);
        if (!str_contains($head, 'MySQL dump') && !str_contains($head, 'MariaDB dump')) {
            @unlink($target);
            return false;
        }
        return true;
    }

    /** Pure-PHP logical dump: schema + data, batched multi-row INSERTs. */
    private function phpDump(string $target): void
    {
        $pdo = $this->db->pdo();
        $gz = gzopen($target, 'wb6');
        if ($gz === false) {
            throw new RuntimeException('Cannot write ' . $target);
        }
        $w = static function (string $s) use ($gz): void {
            gzwrite($gz, $s);
        };

        $w("-- Almarah Foundation database backup (pure-PHP dump)\n");
        $w('-- Database: ' . $this->dbConfig['database'] . "\n-- Generated: " . gmdate('c') . "\n\n");
        $w("SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\nSET time_zone='+00:00';\n\n");

        $tables = $pdo->query('SHOW FULL TABLES')->fetchAll(PDO::FETCH_NUM);
        foreach ($tables as [$table, $type]) {
            $q = '`' . str_replace('`', '``', (string) $table) . '`';
            if ($type === 'VIEW') {
                $create = $pdo->query("SHOW CREATE VIEW {$q}")->fetch(PDO::FETCH_ASSOC);
                $w("DROP VIEW IF EXISTS {$q};\n" . ($create['Create View'] ?? '') . ";\n\n");
                continue;
            }
            $create = $pdo->query("SHOW CREATE TABLE {$q}")->fetch(PDO::FETCH_ASSOC);
            $w("DROP TABLE IF EXISTS {$q};\n" . ($create['Create Table'] ?? '') . ";\n\n");

            $stmt = $pdo->query("SELECT * FROM {$q}");
            $cols = null;
            $batch = [];
            $batchBytes = 0;
            $flush = static function () use (&$batch, &$batchBytes, &$cols, $q, $w): void {
                if ($batch === []) {
                    return;
                }
                $w("INSERT INTO {$q} (" . implode(',', $cols) . ") VALUES\n" . implode(",\n", $batch) . ";\n");
                $batch = [];
                $batchBytes = 0;
            };
            while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
                if ($cols === null) {
                    $cols = array_map(static fn ($c) => '`' . str_replace('`', '``', (string) $c) . '`', array_keys($row));
                }
                $vals = [];
                foreach ($row as $v) {
                    if ($v === null) {
                        $vals[] = 'NULL';
                    } elseif (is_int($v) || is_float($v)) {
                        $vals[] = (string) $v;
                    } else {
                        $vals[] = $pdo->quote((string) $v);
                    }
                }
                $line = '(' . implode(',', $vals) . ')';
                $batch[] = $line;
                $batchBytes += strlen($line);
                if (count($batch) >= 500 || $batchBytes > 900_000) {
                    $flush();
                }
            }
            $flush();
            $w("\n");
        }
        $w("SET FOREIGN_KEY_CHECKS=1;\n");
        gzclose($gz);
    }

    // ------------------------------------------------------------ uploads

    private function zipUploads(string $target, bool $includeDatabase): void
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('The PHP zip extension is not enabled on this server.');
        }
        $zip = new ZipArchive();
        if ($zip->open($target, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Cannot create ' . $target);
        }

        $roots = [
            'storage/uploads' => $this->basePath . '/storage/uploads',
            'storage/private' => $this->basePath . '/storage/private',
            'uploads'         => $this->basePath . '/uploads',
        ];
        $count = 0;
        foreach ($roots as $prefix => $dir) {
            if (!is_dir($dir)) {
                continue;
            }
            $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));
            foreach ($it as $file) {
                /** @var \SplFileInfo $file */
                if (!$file->isFile() || $file->getFilename() === '.gitkeep') {
                    continue;
                }
                $rel = $prefix . '/' . ltrim(str_replace('\\', '/', substr($file->getPathname(), strlen($dir))), '/');
                $zip->addFile($file->getPathname(), $rel);
                $count++;
            }
        }

        if ($includeDatabase) {
            $tmp = tempnam(sys_get_temp_dir(), 'almdb');
            if ($tmp === false) {
                throw new RuntimeException('Cannot create a temporary file.');
            }
            $this->dumpDatabaseGz($tmp);
            $zip->addFile($tmp, 'database.sql.gz');
        }

        $zip->addFromString('README.txt', "Almarah Foundation backup\nCreated: " . gmdate('c') . "\nFiles: {$count}\n"
            . ($includeDatabase ? "Includes database.sql.gz — restore with: gunzip < database.sql.gz | mysql -u USER -p DBNAME\n" : '')
            . "Restore uploads by copying the storage/ and uploads/ folders back into the site root.\n");

        if (!$zip->close()) {
            throw new RuntimeException('Zip could not be finalised: ' . $zip->getStatusString());
        }
        if (isset($tmp)) {
            @unlink($tmp);
        }
    }

    // --------------------------------------------------------- encryption

    /**
     * Encrypts in place to <file>.enc: 8-byte magic, 16-byte salt, 16-byte IV,
     * ciphertext, 32-byte HMAC-SHA256. Key = PBKDF2(passphrase, salt).
     * Decrypt with `php bin/console backups:decrypt <file>`.
     */
    private function encryptFile(string $file): void
    {
        $pass = (string) $this->config['passphrase'];
        $salt = random_bytes(16);
        $iv = random_bytes(16);
        [$encKey, $macKey] = $this->deriveKeys($pass, $salt);

        $plain = file_get_contents($file);
        if ($plain === false) {
            throw new RuntimeException('Cannot read ' . $file);
        }
        $cipher = openssl_encrypt($plain, self::CIPHER, $encKey, OPENSSL_RAW_DATA, $iv);
        if ($cipher === false) {
            throw new RuntimeException('Encryption failed.');
        }
        $body = 'ALMBKP01' . $salt . $iv . $cipher;
        $mac = hash_hmac('sha256', $body, $macKey, true);
        if (file_put_contents($file . '.enc', $body . $mac, LOCK_EX) === false) {
            throw new RuntimeException('Cannot write encrypted backup.');
        }
        unlink($file);
    }

    public function decryptFile(string $encFile, string $outFile, string $passphrase): void
    {
        $data = file_get_contents($encFile);
        if ($data === false || strlen($data) < 8 + 16 + 16 + 32 || substr($data, 0, 8) !== 'ALMBKP01') {
            throw new RuntimeException('Not an Almarah encrypted backup.');
        }
        $salt = substr($data, 8, 16);
        $iv = substr($data, 24, 16);
        $mac = substr($data, -32);
        $body = substr($data, 0, -32);
        $cipher = substr($body, 40);
        [$encKey, $macKey] = $this->deriveKeys($passphrase, $salt);
        if (!hash_equals(hash_hmac('sha256', $body, $macKey, true), $mac)) {
            throw new RuntimeException('Wrong passphrase or corrupted file (HMAC mismatch).');
        }
        $plain = openssl_decrypt($cipher, self::CIPHER, $encKey, OPENSSL_RAW_DATA, $iv);
        if ($plain === false) {
            throw new RuntimeException('Decryption failed.');
        }
        file_put_contents($outFile, $plain, LOCK_EX);
    }

    /** @return array{0:string,1:string} */
    private function deriveKeys(string $pass, string $salt): array
    {
        $key = hash_pbkdf2('sha256', $pass, $salt, 200_000, 64, true);
        return [substr($key, 0, 32), substr($key, 32, 32)];
    }

    // -------------------------------------------------------------- misc

    /** @return array{0:string,1:string} offsite_status, message */
    private function pushOffsite(string $file): array
    {
        $tpl = trim((string) $this->config['offsite_cmd']);
        if (!function_exists('exec')) {
            return ['failed', 'exec() disabled'];
        }
        $cmd = str_replace('{file}', escapeshellarg($file), $tpl) . ' 2>&1';
        $out = [];
        $code = 1;
        @exec($cmd, $out, $code);
        $tail = mb_substr(implode(' ', $out), 0, 300);
        return $code === 0 ? ['uploaded', 'uploaded' . ($tail !== '' ? " ({$tail})" : '')] : ['failed', "exit {$code} {$tail}"];
    }

    /** Deny web access in case the folder ends up under a document root. */
    private function protectFolder(string $dir): void
    {
        if (!is_file($dir . '/.htaccess')) {
            @file_put_contents($dir . '/.htaccess', "Require all denied\nDeny from all\n");
        }
        if (!is_file($dir . '/index.html')) {
            @file_put_contents($dir . '/index.html', '');
        }
    }
}
