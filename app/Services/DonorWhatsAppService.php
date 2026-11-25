<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\DonationRepository;

/**
 * Sends the donor pack PDF (receipt + thank-you / tax-exemption letter + approvals) that the "Download PDF" button
 * serves, to the donor's WhatsApp number. Used for every kind of donation: online, bank, offline and in-kind.
 */
final class DonorWhatsAppService
{
    public function __construct(
        private WetarseelClient $api,
        private DonationRepository $donations,
        private DonationCertificateService $certificates,
        private InKindDonationService $inkind,
        private InKindCertificateService $inkindCertificates,
        private AuditService $audit
    ) {
    }

    public function isConfigured(): bool
    {
        return $this->api->isConfigured();
    }

    /** Money donation of any source: sends the donor pack on file for that donation. @return array{ok:bool,message:string} */
    public function sendDonation(int $donationId, ?int $actor): array
    {
        if (!$this->api->isConfigured()) {
            return $this->fail('The WhatsApp API is not set up yet (WETARSEEL_API_KEY).');
        }
        $donation = $this->donations->find($donationId);
        if ($donation === null) {
            return $this->fail('Donation not found.');
        }
        $cert = $this->certificates->find($donationId);
        if ($cert) {
            $cert = $this->certificates->upgradeIfOutdated($cert); // same as Download PDF: old layouts are rebuilt as the donor pack
        }
        $path = $cert && $cert['status'] === 'generated' ? $this->certificates->absolutePath($cert) : null;
        if ($path === null) {
            return $this->fail('Generate the certificate first.');
        }
        $phone = WetarseelClient::phone((string) ($donation['donor_phone'] ?? ''));
        if ($phone === null) {
            return $this->fail('This donation has no valid donor phone number.');
        }
        $amount = trim((string) ($donation['currency'] ?? '') . ' ' . number_format(((int) ($donation['amount_minor'] ?? 0)) / 100, 2));
        $name = trim((string) ($donation['donor_name'] ?? '')) ?: 'Donor';
        $r = $this->api->sendTemplateWithFile($phone, $this->api->template(), [$name, 'donation of ' . $amount], $path, (string) $cert['certificate_number'] . '.pdf');

        $this->audit->log($r['ok'] ? 'donation.certificate.whatsapp_sent' : 'donation.certificate.whatsapp_failed', 'donation', $donationId, [
            'to' => '+' . $phone, 'certificate' => $cert['certificate_number'], 'message_id' => $r['id'], 'error' => $r['error'],
        ], $actor);
        return $r['ok']
            ? ['ok' => true, 'message' => 'Donor PDF sent to +' . $phone . ' on WhatsApp.']
            : $this->fail('WhatsApp message not sent: ' . $r['error']);
    }

    /** In-kind donation: sends the chosen certificate version. @return array{ok:bool,message:string,inkind_id:int} */
    public function sendInKind(int $certId, ?int $actor): array
    {
        $cert = $this->inkindCertificates->find($certId);
        $inkindId = (int) ($cert['inkind_id'] ?? 0);
        if (!$this->api->isConfigured()) {
            return $this->fail('The WhatsApp API is not set up yet (WETARSEEL_API_KEY).', $inkindId);
        }
        $path = $cert && $cert['status'] === 'generated' ? $this->inkindCertificates->absolutePath($cert) : null;
        if ($path === null) {
            return $this->fail('That certificate has no PDF on file.', $inkindId);
        }
        $d = $this->inkind->find($inkindId);
        $phone = $d ? WetarseelClient::phone((string) ($d['donor_phone'] ?? '')) : null;
        if ($phone === null) {
            return $this->fail('This donation has no valid donor phone number.', $inkindId);
        }
        $name = trim((string) ($d['donor_name'] ?? '')) ?: 'Donor';
        $r = $this->api->sendTemplateWithFile($phone, $this->api->template(), [$name, 'donation of goods'], $path, (string) $cert['certificate_number'] . '.pdf');

        $this->audit->log($r['ok'] ? 'inkind.certificate.whatsapp_sent' : 'inkind.certificate.whatsapp_failed', 'inkind_donation', $inkindId, [
            'to' => '+' . $phone, 'certificate' => $cert['certificate_number'], 'message_id' => $r['id'], 'error' => $r['error'],
        ], $actor);
        $this->inkind->event($inkindId, 'certificate_whatsapp', $actor, 'WhatsApp to +' . $phone . ($r['ok'] ? ': sent.' : ': failed (' . $r['error'] . ').'));
        return $r['ok']
            ? ['ok' => true, 'message' => 'Donor PDF sent to +' . $phone . ' on WhatsApp.', 'inkind_id' => $inkindId]
            : $this->fail('WhatsApp message not sent: ' . $r['error'], $inkindId);
    }

    /** @return array{ok:false,message:string,inkind_id:int} */
    private function fail(string $message, int $inkindId = 0): array
    {
        return ['ok' => false, 'message' => $message, 'inkind_id' => $inkindId];
    }
}
