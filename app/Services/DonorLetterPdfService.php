<?php

declare(strict_types=1);

namespace App\Services;

require_once __DIR__ . '/../Support/Pdf/fpdf.php';
require_once __DIR__ . '/../Support/Pdf/PdfAppender.php';

use App\Support\Pdf\PdfAppender;
use InvalidArgumentException;
use RuntimeException;

/**
 * Donor pack as one PDF, on the foundation letterhead:
 *   page 1  donation receipt (landscape, styled like the Certificate of Appreciation; system generated, only filled-in details shown)
 *   page 2  thank-you letter (original wording; only the donor name changes)
 *   page 3+ FBR approval order, Shariah approval for Zakat, Punjab Charity Commission certificate (static pages)
 * Two kinds: 'donation' (cash / online / cheque) and 'inkind' (goods). No database needed.
 */
final class DonorLetterPdfService
{
    private const WINE = [150, 25, 62];
    private const DARK = [30, 20, 25];
    private const MUTED = [110, 100, 105];
    private const GOLD = [242, 194, 0];

    private string $root;
    private array $cfg;

    public function __construct(?string $root = null, ?array $config = null)
    {
        $this->root = $root ?? dirname(__DIR__, 2);
        $this->cfg = $config ?? require $this->root . '/config/donor_letter.php';
    }

    /**
     * @param array{kind?:string,donor_name:string,date?:string,donation_no?:string,salutation?:string,contact?:string,reference?:string,amount?:string,amount_words?:string,txn_no?:string,purpose?:string,mode?:string,inkind_items?:string,attachments?:bool} $d
     *   mode: cash|online|cheque|inkind (inkind kind forces 'inkind'); amount = number only, e.g. 25000
     */
    public function render(array $d): string
    {
        $kind = ($d['kind'] ?? 'donation') === 'inkind' ? 'inkind' : 'donation';
        $name = trim((string) ($d['donor_name'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('Donor name is required.');
        }
        $name = mb_substr($name, 0, 80);
        $mode = $kind === 'inkind' ? 'inkind' : strtolower((string) ($d['mode'] ?? ''));

        $pdf = new \FPDF('P', 'mm', 'A4');
        $pdf->SetAutoPageBreak(false);
        $pdf->SetTitle($this->t('Donation receipt and thank-you letter - ' . $name), false);
        $pdf->SetAuthor($this->t($this->cfg['org_name']), false);

        $salutation = array_key_exists('salutation', $d) ? trim((string) $d['salutation']) : (string) $this->cfg['letter']['salutation'];
        $this->receiptPage($pdf, $d, $name, $kind, $mode);
        $this->letterPage($pdf, $name, $salutation);
        $out = $pdf->Output('S');

        if (($d['attachments'] ?? true) !== false) {
            $file = $this->root . '/assets/documents/Donor-Letter-Attachments.pdf';
            $extra = is_file($file) ? file_get_contents($file) : false;
            if ($extra === false || $extra === '') {
                throw new RuntimeException('Donor-Letter-Attachments.pdf is missing from assets/documents.');
            }
            $out = PdfAppender::append($out, $extra);
        }
        return $out;
    }

    private function letterhead(\FPDF $pdf): void
    {
        $pdf->AddPage('P');
        $bg = $this->root . '/assets/images/letterhead/almarah-letterhead.jpg';
        if (is_file($bg)) {
            $pdf->Image($bg, 6, 9, 198, 280);   // small margin on every side, header no longer touches the top edge
        }
    }

    // ---------------------------------------------------------------- receipt
    // Same look as the Certificate of Appreciation (wine/gold frame), A4 portrait. System generated: no signature.
    private function receiptPage(\FPDF $pdf, array $d, string $name, string $kind, string $mode): void
    {
        $c = $this->cfg;
        $pdf->AddPage('P');
        $pdf->SetDrawColor(...self::WINE);
        $pdf->SetLineWidth(1.2);
        $pdf->Rect(12, 12, 186, 273);
        $pdf->SetDrawColor(...self::GOLD);
        $pdf->SetLineWidth(0.5);
        $pdf->Rect(15, 15, 180, 267);
        $x = 25;
        $w = 160;

        $logo = $this->root . '/assets/images/logo/header-logo-01.png';
        if (is_file($logo)) {
            try {
                $pdf->Image($logo, 90, 28, 30, 0);
            } catch (\Throwable) {
            }
        }
        $pdf->SetXY($x, 56);
        $pdf->SetFont('Helvetica', 'B', 13);
        $pdf->SetTextColor(...self::WINE);
        $pdf->Cell($w, 6, $this->t(strtoupper($c['org_name'])), 0, 1, 'C');
        $pdf->SetX($x);
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor(...self::MUTED);
        $pdf->Cell($w, 5, $this->t($c['org_address'] . '  |  NTN ' . $c['ntn']), 0, 1, 'C');

        $pdf->Ln(8);
        $pdf->SetX($x);
        $pdf->SetFont('Helvetica', 'B', 28);
        $pdf->SetTextColor(...self::DARK);
        $pdf->Cell($w, 13, 'Donation Receipt', 0, 1, 'C');
        $pdf->SetFillColor(...self::GOLD);
        $pdf->Rect(85, $pdf->GetY() + 1, 40, 1, 'F');

        $pdf->Ln(12);
        $pdf->SetX($x);
        $pdf->SetFont('Helvetica', '', 11);
        $pdf->SetTextColor(...self::MUTED);
        $pdf->Cell($w, 6, 'Received with thanks from', 0, 1, 'C');
        $pdf->Ln(2);
        $nm = $this->t($name);
        $pdf->SetFont('Helvetica', 'BI', 24);
        $pdf->SetFont('Helvetica', 'BI', $pdf->GetStringWidth($nm) > $w - 6 ? 15 : 24);
        $pdf->SetTextColor(...self::WINE);
        $pdf->SetX($x);
        $pdf->Cell($w, 12, $nm, 0, 1, 'C');

        $amount = trim((string) ($d['amount'] ?? ''));
        $num = $amount !== '' ? (float) str_replace(',', '', $amount) : 0.0;
        $methods = ['cash' => 'Cash', 'online' => 'Online Transfer', 'cheque' => 'Cheque', 'inkind' => 'Donation in Kind'];
        $cur = strtoupper(preg_replace('/[^A-Za-z]/', '', (string) ($d['currency'] ?? 'PKR')) ?: 'PKR');
        $shown = $num > 0 ? number_format($num, floor($num) == $num ? 0 : 2) : '';
        $words = $num > 0 ? (trim((string) ($d['amount_words'] ?? '')) ?: self::amountInWords($amount, $cur)) : '';

        // Every field is always printed; an empty one stays a blank line.
        $rows = [
            [['Donation No.', (string) ($d['donation_no'] ?? '')], ['Date', (string) ($d['date'] ?? '')]],
            [['Contact No.', (string) ($d['contact'] ?? '')], ['Reference By', (string) ($d['reference'] ?? '')]],
        ];
        if ($kind === 'inkind') {
            $rows[] = [['Payment method', 'Donation in Kind']];
            $rows[] = [['Items donated', (string) ($d['inkind_items'] ?? '')]];
        } else {
            $rows[] = [['Amount (' . $cur . ')', $shown], ['Payment method', $methods[$mode] ?? '']];
            $rows[] = [['Amount in words', $words]];
            $rows[] = [['Slip / Cheque / Transaction No.', (string) (($d['txn_no'] ?? '') ?: ($d['cheque_no'] ?? ''))]];
        }
        $rows[] = [['Donation purpose', (string) ($d['purpose'] ?? '')]];

        $y = $pdf->GetY() + 14;
        $colW = $w / 2;
        foreach ($rows as $row) {
            foreach ($row as $i => [$label, $value]) {
                $cw = count($row) === 1 ? $w : $colW - 6;
                $cx = $x + $i * $colW;
                $pdf->SetXY($cx, $y);
                $pdf->SetTextColor(...self::MUTED);
                $pdf->SetFont('Helvetica', '', 8.5);
                $pdf->Cell($cw, 4.5, $this->t(strtoupper($label)), 0, 0, 'L');
                $size = 12;
                $pdf->SetFont('Helvetica', 'B', $size);
                $text = $this->t(trim($value));
                while ($size > 7 && $pdf->GetStringWidth($text) > $cw - 2) {
                    $pdf->SetFont('Helvetica', 'B', $size -= 0.5);
                }
                $pdf->SetTextColor(...self::DARK);
                $pdf->SetXY($cx, $y + 5);
                $pdf->Cell($cw, 7, $text, 0, 0, 'L');
                $pdf->SetDrawColor(...self::GOLD);
                $pdf->SetLineWidth(0.4);
                $pdf->Line($cx, $y + 12.5, $cx + $cw, $y + 12.5);
            }
            $y += 21;
        }

        $pdf->SetXY($x, 268);
        $pdf->SetFont('Helvetica', 'I', 8.5);
        $pdf->SetTextColor(...self::MUTED);
        $pdf->Cell($w, 4, 'This is a system-generated document and does not require a signature.', 0, 0, 'C');
    }

    // ----------------------------------------------------------------- letter
    private function letterPage(\FPDF $pdf, string $name, string $salutation): void
    {
        $this->letterhead($pdf);
        $c = $this->cfg;
        $L = $c['letter'];
        $x = 33;   // left/right margin
        $w = 144;

        $pdf->SetTextColor(...self::DARK);
        $pdf->SetXY($x, 72);   // clear space below the letterhead
        // Greeting: "My Dear" + bold name + salutation
        $pdf->SetFont('Helvetica', '', 13);
        $pdf->Write(7, $this->t('My Dear '));
        $pdf->SetFont('Helvetica', 'B', 13);
        $pdf->Write(7, $this->t($name));
        $pdf->SetFont('Helvetica', '', 13);
        $pdf->Write(7, $this->t(($salutation !== '' ? ' ' . $salutation : '') . ','));
        $pdf->Ln(11);
        foreach (['p1', 'p2', 'ask', 'close'] as $k) {
            $pdf->SetX($x);
            $pdf->SetFont('Helvetica', '', 12.5);
            $pdf->MultiCell($w, 7, $this->t($L[$k]), 0, 'J');
            $pdf->Ln(4);
        }
        $this->signOff($pdf, 112, $pdf->GetY() + 4, $L['regards']);
    }

    private function signOff(\FPDF $pdf, float $sx, float $y, string $closing): void
    {
        $c = $this->cfg;
        $pdf->SetXY($sx, $y);
        $pdf->SetFont('Helvetica', '', 12.5);
        $pdf->SetTextColor(...self::DARK);
        $pdf->Cell(70, 6.5, $closing, 0, 1, 'L');
        $sig = $this->root . '/assets/images/signature/chairperson-signature.png';
        $sy = $pdf->GetY();
        if (is_file($sig)) {
            $pdf->Image($sig, $sx - 2, $sy + 1, 34, 0);
        }
        $pdf->SetXY($sx, $sy + 22);
        $pdf->SetFont('Helvetica', 'B', 13);
        $pdf->Cell(70, 6.5, $this->t($c['signatory_name']), 0, 2, 'L');
        $pdf->SetFont('Helvetica', '', 12.5);
        $pdf->Cell(70, 6.5, $this->t($c['signatory_title']), 0, 2, 'L');
        $pdf->Cell(70, 6.5, $this->t($c['org_name']), 0, 2, 'L');
    }

    // ------------------------------------------- data mapping for the admin screens

    /** Build render() input from a donations row (as returned by DonationRepository::find). */
    public static function fromDonation(array $d, ?string $nameOverride = null): array
    {
        $method = strtolower(trim((string) ($d['payment_method'] ?? '')));
        $mode = match (true) {
            $method === '' && trim((string) ($d['gateway_name'] ?? '')) === '' => '',
            str_contains($method, 'cheque') || str_contains($method, 'check') => 'cheque',
            str_contains($method, 'cash') => 'cash',
            default => 'online',
        };
        $stamp = (string) (($d['transaction_at'] ?? '') ?: ($d['completed_at'] ?? '') ?: ($d['created_at'] ?? ''));
        $date = $stamp !== '' ? date('d-m-Y', (int) strtotime($stamp)) : '';
        $name = $nameOverride ?? (
            ((int) ($d['acknowledgment_self'] ?? 1)) === 0 && trim((string) ($d['recipient_name'] ?? '')) !== ''
                ? trim((string) $d['recipient_name'])
                : (trim((string) ($d['donor_name'] ?? '')) ?: 'Donor')
        );
        return [
            'kind' => 'donation',
            'donor_name' => $name,
            'date' => $date,
            'donation_no' => (string) ($d['public_reference'] ?? ''),
            'contact' => (string) ($d['donor_phone'] ?? ''),
            'reference' => (string) ($d['referrer_name'] ?? ''),   // person who referred the donor
            'amount' => number_format(((int) ($d['amount_minor'] ?? 0)) / 100, 2, '.', ''),
            'currency' => (string) ($d['currency'] ?? 'PKR'),
            'mode' => $mode,
            'txn_no' => (string) ($d['bank_reference'] ?? ''),     // slip / cheque / transaction number
            'purpose' => (string) ($d['purpose_name'] ?? ''),
        ];
    }

    /** Build render() input from an inkind_donations row and its item lines (already formatted text). */
    public static function fromInKind(array $d, array $lines, string $name): array
    {
        $shown = array_slice($lines, 0, 6);
        $more = count($lines) - count($shown);
        $date = substr((string) (($d['handover_date'] ?? '') ?: ($d['created_at'] ?? '')), 0, 10);
        return [
            'kind' => 'inkind',
            'donor_name' => $name,
            'date' => $date !== '' ? date('d-m-Y', (int) strtotime($date)) : '',
            'donation_no' => (string) ($d['public_reference'] ?? ''),
            'contact' => (string) ($d['donor_phone'] ?? ''),
            'reference' => (string) (($d['referral_enabled'] ?? 0) ? ($d['referrer_name'] ?? '') : ''),
            'inkind_items' => implode(', ', $shown) . ($more > 0 ? ' and ' . $more . ' more' : ''),
        ];
    }

    private function t(string $s): string
    {
        $r = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $s);
        return $r === false ? '?' : $r;
    }

    // -------------------------------------------------------- amount in words
    public static function amountInWords(string $amount, string $currency = 'PKR'): string
    {
        $value = round((float) str_replace(',', '', $amount), 2);
        $n = (int) floor($value);
        $minor = (int) round(($value - $n) * 100);
        $whole = $n;
        if ($n <= 0 && $minor <= 0) {
            return '';
        }
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        $below100 = static fn (int $v): string => $v < 20 ? $ones[$v] : $tens[intdiv($v, 10)] . ($v % 10 ? ' ' . $ones[$v % 10] : '');
        $below1000 = static function (int $v) use ($below100, $ones): string {
            $s = $v >= 100 ? $ones[intdiv($v, 100)] . ' Hundred' : '';
            $r = $v % 100;
            return trim($s . ($r ? ($s ? ' ' : '') . $below100($r) : ''));
        };
        $parts = [];
        foreach ([10000000 => 'Crore', 100000 => 'Lakh', 1000 => 'Thousand'] as $unit => $label) {
            if ($n >= $unit) {
                $parts[] = $below1000(intdiv($n, $unit)) . ' ' . $label;
                $n %= $unit;
            }
        }
        if ($n > 0) {
            $parts[] = $below1000($n);
        }
        $pkr = $currency === 'PKR';
        $text = $whole > 0 ? implode(' ', $parts) . ($pkr ? ' Rupees' : ' ' . $currency) : '';
        if ($minor > 0) {
            $text .= ($text !== '' ? ' and ' : '') . $below100($minor) . ($pkr ? ' Paisa' : ' Cents');
        }
        return $text . ' Only';
    }
}
