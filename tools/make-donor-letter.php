<?php

declare(strict_types=1);

/**
 * Generate a donor pack PDF from the command line (no database needed).
 *
 *   php tools/make-donor-letter.php "Ahmed Khan"                       -> donation pack
 *   php tools/make-donor-letter.php "Ahmed Khan" inkind                -> in-kind pack
 *   php tools/make-donor-letter.php "Ahmed Khan" donation 25000        -> with amount (words filled automatically)
 *
 * Output: storage/cache/donor-letter-<kind>.pdf
 */

require __DIR__ . '/../app/Services/DonorLetterPdfService.php';

$name = $argv[1] ?? '';
$kind = $argv[2] ?? 'donation';
$amount = $argv[3] ?? '';
if ($name === '') {
    fwrite(STDERR, "Usage: php tools/make-donor-letter.php \"Donor Name\" [donation|inkind] [amount]\n");
    exit(1);
}
$pdf = (new App\Services\DonorLetterPdfService())->render([
    'kind' => $kind,
    'donor_name' => $name,
    'date' => date('d-m-Y'),
    'amount' => $amount,
    'mode' => $kind === 'inkind' ? 'inkind' : 'cash',
]);
$dir = __DIR__ . '/../storage/cache';
is_dir($dir) || mkdir($dir, 0775, true);
$file = $dir . '/donor-letter-' . $kind . '.pdf';
file_put_contents($file, $pdf);
echo "Created " . realpath($file) . "\n";
