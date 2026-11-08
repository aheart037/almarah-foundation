<?php
/** Donation tax exemption certificate email (PDF attached). */
require __DIR__ . '/_partials.php';

$orgName = (string) (is_array($org ?? null) ? ($org['legal_name'] ?? '') : '');
$orgName = $orgName !== '' ? $orgName : (string) config('app.name', 'Almarah Foundation');
$name    = trim((string) ($donor_name ?? ''));
$heading = (string) ($heading ?? 'Your tax exemption certificate');
$greeting = $name !== '' ? 'Dear ' . $name . ',' : 'Hello,';
?>
<?= email_heading($heading, 'Tax exemption certificate') ?>
<?= email_paragraph($greeting . ' thank you for your generous donation. Your donation has been verified by our finance team and your tax exemption certificate is attached to this email as a PDF.') ?>
<?= email_amount('Your verified donation', (string) ($amount ?? '')) ?>
<?= email_rows([
    'Certificate number' => (string) ($certificate_number ?? ''),
    'Donation reference' => (string) ($reference ?? ''),
    'Donation date'      => (string) ($donation_date ?? ''),
], 'Certificate details') ?>
<?= email_note('Please keep this certificate for your records. If you did not receive the attachment or need it reissued, reply to this email and quote the certificate number.') ?>
<?= email_signoff($orgName, 'With gratitude,') ?>
