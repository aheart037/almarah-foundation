<?php
/** Thank-you certificate email for a verified in-kind (goods) donation (PDF attached). */
require __DIR__ . '/_partials.php';

$orgName = (string) (is_array($org ?? null) ? ($org['legal_name'] ?? '') : '');
$orgName = $orgName !== '' ? $orgName : (string) config('app.name', 'Almarah Foundation');
$name    = trim((string) ($donor_name ?? ''));
$heading = (string) ($heading ?? 'Thank you for your donation of goods');
$greeting = $name !== '' ? 'Dear ' . $name . ',' : 'Hello,';
$for     = trim((string) ($certificate_name ?? ''));
?>
<?= email_heading($heading, 'Certificate of appreciation') ?>
<?= email_paragraph($greeting . ' thank you for your generous donation of goods. Our team has received and verified it' . ($for !== '' && $for !== $name ? ', and your certificate of appreciation is issued in the name of ' . $for : '') . '. The certificate is attached to this email as a PDF.') ?>
<?= email_rows(array_filter([
    'Donated items'      => (string) ($items_summary ?? ''),
    'Donation reference' => (string) ($reference ?? ''),
    'Certificate number' => (string) ($certificate_number ?? ''),
]), 'Donation details') ?>
<?= email_note('Your kindness helps us feed the hungry, parent the orphan and educate Pakistan. If you did not receive the attachment or need it reissued, reply to this email and quote the certificate number.') ?>
<?= email_signoff($orgName, 'With gratitude,') ?>
