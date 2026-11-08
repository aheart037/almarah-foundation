<?php

declare(strict_types=1);

namespace App\Services;

require_once __DIR__ . '/../Support/Pdf/fpdf.php';

/**
 * Renders the donation tax exemption certificate (A4 landscape) with the bundled FPDF.
 * Every legal/registration value is supplied by the caller from stored settings; nothing is hard-coded here.
 * FPDF core fonts cover Western European text only; other scripts in a donor name print as "?".
 */
final class CertificatePdfService
{
    private const WINE = [107, 15, 53];
    private const GOLD = [242, 194, 0];
    private const DARK = [26, 8, 16];
    private const MUTED = [110, 100, 105];

    /**
     * @param array{donor_name:string,amount:string,donation_date:string,reference:string,certificate_number:string,issue_date:string,org_name:string,org_address:string,ntn:string,approval_text:string,valid_from:string,valid_until:string,signatory_name:string,signatory_title:string,logo:?string,compress?:bool} $c
     */
    public function render(array $c): string
    {
        $pdf = new CertificatePdfDocument('L', 'mm', 'A4');
        if (($c['compress'] ?? true) === false) {
            $pdf->SetCompression(false);
        }
        $pdf->SetTitle($pdf->t('Tax Exemption Certificate ' . $c['certificate_number']), false);
        $pdf->SetAuthor($pdf->t($c['org_name']), false);
        $pdf->SetCreator($pdf->t($c['org_name']), false);
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();

        // Frame
        $pdf->SetDrawColor(...self::WINE);
        $pdf->SetLineWidth(1.2);
        $pdf->Rect(8, 8, 281, 194);
        $pdf->SetDrawColor(...self::GOLD);
        $pdf->SetLineWidth(0.5);
        $pdf->Rect(11, 11, 275, 188);

        // Logo + organisation
        $logo = $c['logo'] ?? null;
        if ($logo !== null && is_file($logo)) {
            try {
                $pdf->Image($logo, 133.5, 15, 30, 0);
            } catch (\Throwable) {
                // unsupported image: the organisation name below still identifies the issuer
            }
        }
        $pdf->SetXY(10, 47);
        $pdf->SetFont('Helvetica', 'B', 13);
        $pdf->SetTextColor(...self::WINE);
        $pdf->Cell(277, 6, $pdf->t(strtoupper($c['org_name'])), 0, 1, 'C');
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor(...self::MUTED);
        $pdf->Cell(277, 5, $pdf->t($c['org_address'] . '  |  NTN / Registration No. ' . $c['ntn']), 0, 1, 'C');

        // Title
        $pdf->Ln(4);
        $pdf->SetFont('Helvetica', 'B', 30);
        $pdf->SetTextColor(...self::DARK);
        $pdf->Cell(277, 14, $pdf->t('Tax Exemption Certificate'), 0, 1, 'C');
        $pdf->SetFillColor(...self::GOLD);
        $pdf->Rect(128.5, $pdf->GetY() + 1, 40, 1, 'F');

        // Body
        $pdf->Ln(8);
        $pdf->SetFont('Helvetica', '', 11);
        $pdf->SetTextColor(...self::MUTED);
        $pdf->Cell(277, 6, $pdf->t('This is to certify that'), 0, 1, 'C');
        $pdf->Ln(1);
        $pdf->SetFont('Helvetica', 'BI', 26);
        $pdf->SetTextColor(...self::WINE);
        $name = $pdf->t($c['donor_name']);
        $pdf->SetFont('Helvetica', 'BI', $pdf->GetStringWidth($name) > 230 ? 18 : 26);
        $pdf->Cell(277, 13, $name, 0, 1, 'C');
        $pdf->SetFont('Helvetica', '', 11);
        $pdf->SetTextColor(...self::MUTED);
        $pdf->Cell(277, 6, $pdf->t('has made a verified donation to ' . $c['org_name'] . ' of'), 0, 1, 'C');
        $pdf->Ln(1);
        $pdf->SetFont('Helvetica', 'B', 22);
        $pdf->SetTextColor(...self::DARK);
        $pdf->Cell(277, 11, $pdf->t($c['amount']), 0, 1, 'C');

        // Details
        $pdf->Ln(3);
        $pdf->SetFont('Helvetica', '', 9.5);
        $y = $pdf->GetY();
        $cols = [
            [15, 'Donation date', $c['donation_date']],
            [78, 'Donation reference', $c['reference']],
            [150, 'Certificate number', $c['certificate_number']],
            [222, 'Issue date', $c['issue_date']],
        ];
        foreach ($cols as [$x, $label, $value]) {
            $pdf->SetXY($x, $y);
            $pdf->SetTextColor(...self::MUTED);
            $pdf->SetFont('Helvetica', '', 8.5);
            $pdf->Cell(64, 4.5, $pdf->t(strtoupper($label)), 0, 2, 'L');
            $pdf->SetTextColor(...self::DARK);
            $pdf->SetFont('Helvetica', 'B', 10.5);
            $pdf->Cell(64, 6, $pdf->t($value), 0, 0, 'L');
        }

        // Tax approval statement (facts as stored in settings only)
        $pdf->SetXY(25, $y + 17);
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor(...self::MUTED);
        $statement = $c['approval_text'] . '. NTN ' . $c['ntn'] . '. Approval valid from '
            . $c['valid_from'] . ' to ' . $c['valid_until'] . '.';
        $pdf->MultiCell(247, 4.6, $pdf->t($statement), 0, 'C');

        // Signature
        $pdf->SetDrawColor(...self::MUTED);
        $pdf->SetLineWidth(0.3);
        $pdf->Line(112, 176, 185, 176);
        $pdf->SetXY(112, 177);
        $pdf->SetFont('Helvetica', 'B', 9.5);
        $pdf->SetTextColor(...self::DARK);
        $pdf->Cell(73, 5, $pdf->t($c['signatory_name'] !== '' ? $c['signatory_name'] : $c['org_name']), 0, 2, 'C');
        $pdf->SetFont('Helvetica', '', 8.5);
        $pdf->SetTextColor(...self::MUTED);
        $pdf->Cell(73, 4.5, $pdf->t($c['signatory_title']), 0, 0, 'C');

        $pdf->SetXY(15, 190);
        $pdf->SetFont('Helvetica', '', 7.5);
        $pdf->Cell(267, 4, $pdf->t('Computer-generated certificate. Quote the certificate number above for any verification request.'), 0, 0, 'C');

        return $pdf->Output('S');
    }
}

class CertificatePdfDocument extends \FPDF
{
    /** UTF-8 to the single-byte encoding FPDF's core fonts expect. */
    public function t(string $s): string
    {
        $s = str_replace(["\u{2013}", "\u{2014}", "\u{2018}", "\u{2019}", "\u{201C}", "\u{201D}"], ['-', '-', "'", "'", '"', '"'], $s);
        $out = function_exists('iconv') ? @iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $s) : false;
        return $out !== false ? $out : mb_convert_encoding($s, 'Windows-1252', 'UTF-8');
    }
}
