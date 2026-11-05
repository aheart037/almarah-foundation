<?php

declare(strict_types=1);

/**
 * Almarah Foundation — install checker (v3.1).
 *
 * Compares the files on this server with V3-RELEASE-MANIFEST.sha256 and lists every
 * file that is MISSING or DIFFERENT, so nothing has to be found one error at a time.
 *
 * Run from the website folder, in cPanel "Terminal" (command line only):
 *     php tools/verify-install.php
 * It only reads files. It prints no passwords and changes nothing.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$root = dirname(__DIR__);
$manifest = $argv[1] ?? $root . '/V3-RELEASE-MANIFEST.sha256';
if (!is_file($manifest)) {
    fwrite(STDERR, "Manifest not found: $manifest\nUpload V3-RELEASE-MANIFEST.sha256 to the website folder first.\n");
    exit(2);
}

// Files intentionally replaced by the v3.1 update: a different hash here is expected.
$updatedInV31 = [
    'index.php', 'routes/admin.php', 'VERSION',
    'resources/views/layouts/admin.php', 'resources/views/public/home.php', 'resources/views/public/donate/index.php',
];

$missing = $different = $updated = [];
$ok = 0;
foreach (file($manifest, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
    if (!preg_match('/^([a-f0-9]{64})\s+\*?(.+)$/i', trim($line), $m)) {
        continue;
    }
    [$hash, $rel] = [strtolower($m[1]), $m[2]];
    $file = $root . '/' . $rel;
    if (!is_file($file)) {
        $missing[] = $rel;
    } elseif (hash_file('sha256', $file) !== $hash) {
        if (in_array($rel, $updatedInV31, true)) { $updated[] = $rel; } else { $different[] = $rel; }
    } else {
        $ok++;
    }
}

printf("Matches V3 manifest: %d\n", $ok);
printf("Updated by v3.1 (expected): %d\n", count($updated));
printf("MISSING on this server: %d\n", count($missing));
foreach ($missing as $f) { echo "  MISSING    $f\n"; }
printf("DIFFERENT from V3: %d\n", count($different));
foreach ($different as $f) { echo "  DIFFERENT  $f\n"; }

$pdfOk = is_file($root . '/app/Support/Pdf/fpdf.php') && is_dir($root . '/app/Support/Pdf/font');
echo $pdfOk ? "\nPDF library: present\n" : "\nPDF library: MISSING -> upload the folder app/Support/Pdf (with its font subfolder). Reports cannot open without it.\n";

exit($missing === [] && $different === [] ? 0 : 1);
