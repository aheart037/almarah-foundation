<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Logger;
use App\Core\Str;
use InvalidArgumentException;
use RuntimeException;

/**
 * Secure image uploads.
 *
 * Defence in depth:
 *  - size limit from configuration;
 *  - extension AND detected MIME must both be on the allowlist;
 *  - the file must parse as an image of an accepted type;
 *  - randomised filename inside a generated storage path;
 *  - the web server is configured to never execute anything in uploads/.
 */
final class UploadService
{
    /** @var array<string,string> extension => expected mime */
    private const ALLOWED = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
    ];

    public function __construct(private Logger $logger, private string $publicPath)
    {
    }

    /**
     * @param array<string,mixed> $file  $_FILES entry
     * @param string              $folder logical subfolder, e.g. "fundraisers"
     * @return string relative path stored in the database, e.g. uploads/fundraisers/ab12.jpg
     */
    public function storeImage(array $file, string $folder): string
    {
        $this->assertUploadOk($file);

        $maxBytes = (int) Config::get('security.uploads.max_bytes', 4194304);
        $size = (int) ($file['size'] ?? 0);

        if ($size <= 0 || $size > $maxBytes) {
            throw new InvalidArgumentException('Images must be smaller than ' . $this->humanBytes($maxBytes) . '.');
        }

        $tmpPath = (string) ($file['tmp_name'] ?? '');
        if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
            throw new InvalidArgumentException('That file could not be read as an upload.');
        }

        // Real content type, not the client-supplied one.
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($tmpPath);

        $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));

        if (!isset(self::ALLOWED[$extension]) || self::ALLOWED[$extension] !== $mime) {
            $this->logger->security('Rejected upload with unexpected type', [
                'extension' => $extension,
                'mime'      => $mime,
            ]);
            throw new InvalidArgumentException('Only JPG, PNG or WebP images are allowed.');
        }

        // Confirm it really is an image and get its dimensions.
        $info = @getimagesize($tmpPath);
        if ($info === false || (int) $info[0] <= 0 || (int) $info[1] <= 0) {
            throw new InvalidArgumentException('That file is not a valid image.');
        }

        $folder = preg_replace('/[^a-z0-9_\-]/i', '', $folder) ?: 'misc';

        $relativeDir = trim((string) Config::get('security.uploads.directory', 'uploads'), '/') . '/' . $folder;
        $absoluteDir = rtrim($this->publicPath, '/') . '/' . $relativeDir;

        if (!is_dir($absoluteDir) && !@mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
            throw new RuntimeException('Unable to create the upload directory.');
        }

        $filename = gmdate('Ymd') . '-' . Str::randomHex(10) . '.' . $extension;
        $absolutePath = $absoluteDir . '/' . $filename;

        if (!move_uploaded_file($tmpPath, $absolutePath)) {
            throw new RuntimeException('Unable to store the uploaded file.');
        }

        @chmod($absolutePath, 0644);

        // Downscale very large images in place to keep pages fast.
        $this->downscale($absolutePath, $mime, (int) Config::get('security.uploads.image_width', 1600));

        $this->logger->info('Upload stored', ['path' => $relativeDir . '/' . $filename]);

        return $relativeDir . '/' . $filename;
    }

    /** Open a remote/uploaded image and resize it if it exceeds the max width. */
    private function downscale(string $path, string $mime, int $maxWidth): void
    {
        if ($maxWidth <= 0 || !function_exists('imagecreatetruecolor')) {
            return;
        }

        $info = @getimagesize($path);
        if ($info === false) {
            return;
        }

        [$width, $height] = $info;
        if ($width <= $maxWidth) {
            return;
        }

        $source = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png'  => @imagecreatefrompng($path),
            'image/webp' => @imagecreatefromwebp($path),
            default      => null,
        };

        if ($source === false || $source === null) {
            return;
        }

        $newWidth = $maxWidth;
        $newHeight = (int) round($height * ($maxWidth / $width));

        $target = imagecreatetruecolor($newWidth, $newHeight);

        if ($mime === 'image/png' || $mime === 'image/webp') {
            imagealphablending($target, false);
            imagesavealpha($target, true);
        }

        imagecopyresampled($target, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        match ($mime) {
            'image/jpeg' => imagejpeg($target, $path, 85),
            'image/png'  => imagepng($target, $path, 8),
            'image/webp' => imagewebp($target, $path, 85),
            default      => null,
        };

        imagedestroy($source);
        imagedestroy($target);
    }

    /** @param array<string,mixed> $file */
    private function assertUploadOk(array $file): void
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($error === UPLOAD_ERR_NO_FILE) {
            throw new InvalidArgumentException('No file was uploaded.');
        }

        $messages = [
            UPLOAD_ERR_INI_SIZE   => 'The file is larger than the server allows.',
            UPLOAD_ERR_FORM_SIZE  => 'The file is larger than the form allows.',
            UPLOAD_ERR_PARTIAL    => 'The file was only partially uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'The server has no temporary folder available.',
            UPLOAD_ERR_CANT_WRITE => 'The server could not write the file to disk.',
            UPLOAD_ERR_EXTENSION  => 'The upload was blocked by a server extension.',
        ];

        if (isset($messages[$error])) {
            throw new InvalidArgumentException($messages[$error]);
        }

        if ($error !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('The upload failed. Please try again.');
        }
    }

    /**
     * Store a non-image document (CV, PDF report). Files go to a PRIVATE
     * directory outside the web root (storage/private/<folder>) and are only
     * ever served through an authenticated admin download route.
     *
     * @param array<string,mixed> $file   $_FILES entry
     * @param list<string>        $allowed extensions, e.g. ['pdf','doc','docx']
     * @return string relative path (private/<folder>/<name>) for the database
     */
    public function storeDocument(array $file, string $folder, array $allowed = ['pdf'], int $maxBytes = 5242880): string
    {
        $this->assertUploadOk($file);

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > $maxBytes) {
            throw new InvalidArgumentException('Files must be smaller than ' . $this->humanBytes($maxBytes) . '.');
        }

        $tmpPath = (string) ($file['tmp_name'] ?? '');
        if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
            throw new InvalidArgumentException('That file could not be read as an upload.');
        }

        $mimeMap = [
            'pdf'  => ['application/pdf'],
            'doc'  => ['application/msword', 'application/CDFV2', 'application/x-ole-storage'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
        ];
        $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($tmpPath);

        if (!in_array($extension, $allowed, true) || !isset($mimeMap[$extension]) || !in_array($mime, $mimeMap[$extension], true)) {
            $this->logger->security('Rejected document upload with unexpected type', ['extension' => $extension, 'mime' => $mime]);
            throw new InvalidArgumentException('Only ' . strtoupper(implode(', ', $allowed)) . ' files are allowed.');
        }

        $folder = preg_replace('/[^a-z0-9_\-]/i', '', $folder) ?: 'misc';
        $relativeDir = 'private/' . $folder;
        $absoluteDir = $this->storageRoot() . '/' . $relativeDir;

        if (!is_dir($absoluteDir) && !@mkdir($absoluteDir, 0770, true) && !is_dir($absoluteDir)) {
            throw new RuntimeException('Unable to create the upload directory.');
        }
        if (!is_file($absoluteDir . '/.htaccess')) {
            @file_put_contents($absoluteDir . '/.htaccess', "Require all denied\n");
        }

        $filename = gmdate('Ymd') . '-' . Str::randomHex(12) . '.' . $extension;
        if (!move_uploaded_file($tmpPath, $absoluteDir . '/' . $filename)) {
            throw new RuntimeException('Unable to store the uploaded file.');
        }
        @chmod($absoluteDir . '/' . $filename, 0640);

        $this->logger->info('Document stored', ['path' => $relativeDir . '/' . $filename]);

        return $relativeDir . '/' . $filename;
    }

    /**
     * Public document (annual report, certificate): same checks as storeDocument
     * but written under the web root so the browser can fetch it directly.
     *
     * @return string relative path, e.g. uploads/reports/20260501-ab12.pdf
     */
    public function storePublicDocument(array $file, string $folder, array $allowed = ['pdf'], int $maxBytes = 15728640): string
    {
        $this->assertUploadOk($file);
        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > $maxBytes) {
            throw new InvalidArgumentException('Files must be smaller than ' . $this->humanBytes($maxBytes) . '.');
        }
        $tmpPath = (string) ($file['tmp_name'] ?? '');
        if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
            throw new InvalidArgumentException('That file could not be read as an upload.');
        }
        $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($tmpPath);
        if (!in_array($extension, $allowed, true) || $extension !== 'pdf' || $mime !== 'application/pdf') {
            $this->logger->security('Rejected public document upload', ['extension' => $extension, 'mime' => $mime]);
            throw new InvalidArgumentException('Only PDF files are allowed here.');
        }
        $folder = preg_replace('/[^a-z0-9_\-]/i', '', $folder) ?: 'misc';
        $relativeDir = 'uploads/' . $folder;
        $absoluteDir = rtrim($this->publicPath, '/') . '/' . $relativeDir;
        if (!is_dir($absoluteDir) && !@mkdir($absoluteDir, 0755, true) && !is_dir($absoluteDir)) {
            throw new RuntimeException('Unable to create the upload directory.');
        }
        $filename = gmdate('Ymd') . '-' . Str::randomHex(12) . '.pdf';
        if (!move_uploaded_file($tmpPath, $absoluteDir . '/' . $filename)) {
            throw new RuntimeException('Unable to store the uploaded file.');
        }
        @chmod($absoluteDir . '/' . $filename, 0644);
        return $relativeDir . '/' . $filename;
    }

    /** The application's own storage folder (refused by .htaccess, never web-served). */
    private function storageRoot(): string
    {
        return dirname(__DIR__, 2) . '/storage';
    }

    /** Absolute path of a document stored by storeDocument(), or null when missing/invalid. */
    public function privatePath(?string $relativePath): ?string
    {
        if ($relativePath === null || !str_starts_with($relativePath, 'private/') || str_contains($relativePath, '..')) {
            return null;
        }
        $abs = $this->storageRoot() . '/' . $relativePath;
        return is_file($abs) ? $abs : null;
    }

    /** Delete a previously stored upload, refusing to leave the uploads directory. */
    public function delete(?string $relativePath): void
    {
        if ($relativePath === null || $relativePath === '') {
            return;
        }

        $relativePath = ltrim($relativePath, '/');

        if (!str_starts_with($relativePath, 'uploads/') || str_contains($relativePath, '..')) {
            return;
        }

        $absolute = rtrim($this->publicPath, '/') . '/' . $relativePath;

        if (is_file($absolute)) {
            @unlink($absolute);
        }
    }

    public function humanBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024) . ' KB';
        }
        return $bytes . ' bytes';
    }

    /** @return array<int,string> */
    public function allowedExtensions(): array
    {
        return array_keys(self::ALLOWED);
    }
}
