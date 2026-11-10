<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use InvalidArgumentException;

require_once __DIR__ . '/../Support/Pdf/fpdf.php';

/** Branded staff-controlled documents; tax exemption requires separate legal approval. */
final class DonationDocumentService
{
    public function __construct(private Database $db, private FinanceService $finance)
    {
    }

    public function render(int $id, string $kind): string
    {
        if (!in_array($kind, ['receipt', 'letter', 'certificate'], true)) throw new InvalidArgumentException('Unknown document.');
        $d = $this->db->selectOne("SELECT d.*, dn.name AS donor_name, dn.email AS donor_email, dn.phone AS donor_phone, g.name AS gateway_name,
            (SELECT pt.provider_transaction_id FROM payment_transactions pt WHERE pt.donation_id=d.id AND pt.status='completed' ORDER BY pt.id DESC LIMIT 1) AS provider_reference
            FROM donations d LEFT JOIN donors dn ON dn.id=d.donor_id LEFT JOIN gateways g ON g.id=d.gateway_id WHERE d.id=:id", ['id' => $id]);
        if (!$d || $d['verification_status'] !== 'verified' || $d['status'] !== 'completed') {
            throw new InvalidArgumentException('Documents require final verification and confirmed payment.');
        }
        if ($kind === 'certificate' && $d['certificate_status'] !== 'issued') {
            throw new InvalidArgumentException('Certificate has not been authorized.');
        }
        $number = $this->finance->assignReceiptNumber($id);
        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 20);
        $logo = ReportPdfService::resolveLogo();
        if ($logo && is_file($logo)) {
            $pdf->Image($logo, 15, 12, 30);
        }
        $pdf->SetY(22);
        $pdf->SetFont('Helvetica', 'B', 19);
        $pdf->SetTextColor(169, 45, 99);
        $pdf->Cell(0, 12, 'ALMARAH FOUNDATION', 0, 1, 'C');
        $pdf->SetDrawColor(169, 45, 99);
        $pdf->Line(20, 45, 190, 45);
        $pdf->Ln(14);
        $title = ['receipt' => 'Donation Receipt', 'letter' => 'Thank-you Letter', 'certificate' => 'Certificate of Appreciation'][$kind];
        $pdf->SetFont('Helvetica', 'B', 17);
        $pdf->Cell(0, 12, $title, 0, 1, 'C');
        $pdf->SetTextColor(35, 25, 30);
        $pdf->SetFont('Helvetica', '', 11);
        $recipient = $d['acknowledgment_self'] ? $d['donor_name'] : ($d['recipient_name'] ?: $d['donor_name']);
        $lines = [
            'Document generated (UTC): ' . gmdate('Y-m-d H:i:s'),
            'Receipt number: ' . $number,
            'Donor: ' . $d['donor_name'],
            'Acknowledgment to: ' . $recipient,
            'Amount: ' . $d['currency'] . ' ' . number_format((int) $d['amount_minor'] / 100, 2),
            'Transaction date: ' . ($d['transaction_at'] ?: $d['completed_at']),
            'Payment method: ' . ($d['payment_method'] ?: ($d['gateway_name'] ?: $d['source'])),
            'Transaction reference: ' . ($d['bank_reference'] ?: ($d['provider_reference'] ?: $d['public_reference'])),
            'Verified (UTC): ' . $d['verified_at'],
        ];
        foreach ($lines as $line) {
            $pdf->MultiCell(0, 8, iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', (string) $line) ?: '?');
        }
        $pdf->Ln(9);
        $message = $kind === 'receipt' ? 'Thank you for your verified contribution. This receipt confirms our record of your donation.' :
            ($kind === 'letter' ? 'Your kindness helps us serve people in need. Thank you for supporting the work of Almarah Foundation.' :
            'Presented in appreciation of your generous support for Almarah Foundation.');
        $pdf->MultiCell(0, 8, $message);
        $pdf->Ln(12);
        $pdf->SetFont('Helvetica', 'I', 9);
        $pdf->MultiCell(0, 6, 'This document does not assert tax deductibility or exemption. Contact the foundation for jurisdiction-specific documentation.');
        return $pdf->Output('S');
    }
}
