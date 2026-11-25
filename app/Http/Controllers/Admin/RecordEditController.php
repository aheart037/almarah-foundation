<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Http\Controllers\Controller;
use App\Repositories\DonationRepository;
use App\Services\AuthService;
use App\Services\FinanceService;
use App\Services\InKindDonationService;
use App\Services\RecordEditService;
use InvalidArgumentException;

/**
 * Edit screens for staff-recorded offline donations (permission donations.edit) and in-kind donations
 * (permission inkind.edit). The routes enforce the permission; RecordEditService enforces which records and
 * which fields may change (see its class comment).
 */
final class RecordEditController extends Controller
{
    public function __construct(
        View $view,
        Session $session,
        Csrf $csrf,
        AuthService $auth,
        private Database $db,
        private DonationRepository $donations,
        private InKindDonationService $inkind,
        private RecordEditService $edit
    ) {
        parent::__construct($view, $session, $csrf, $auth);
    }

    // ------------------------------------------------------------------ offline donations

    public function donationForm(Request $request): Response
    {
        $id = (int) $request->routeParam('id', 0);
        $donation = $this->donations->find($id);
        if ($donation === null) {
            abort(404, 'Donation not found.');
        }
        $access = $this->edit->donationAccess($donation);
        if ($access['mode'] === 'blocked') {
            $this->flashError((string) $access['reason']);
            return $this->redirect('/admin/donations/' . $id);
        }
        $this->prefill($this->donationValues($donation));
        return $this->render('admin/donations/edit', [
            'pageTitle' => 'Edit ' . $donation['public_reference'],
            'donation'  => $donation,
            'access'    => $access,
            'purposes'  => $this->db->select('SELECT id, name FROM donation_purposes ORDER BY sort_order, name'),
            'sources'   => FinanceService::OFFLINE_SOURCES,
            'banks'     => $this->db->select("SELECT id, bank_name, account_number, currency FROM bank_accounts WHERE status = 'published' OR id = :cur ORDER BY is_international, sort_order", ['cur' => (int) ($donation['bank_account_id'] ?? 0)]),
        ], 'layouts/admin');
    }

    public function donationUpdate(Request $request): Response
    {
        $id = (int) $request->routeParam('id', 0);
        $back = '/admin/donations/' . $id . '/edit';
        try {
            $r = $this->edit->updateDonation($id, $request->all(), $request->file('payment_proof'), (int) $this->auth->id());
        } catch (InvalidArgumentException $e) {
            return $this->flashFormStateTo(['form' => $e->getMessage()], $request->all(), $back);
        }
        if ($r['changed'] === []) {
            $this->flashSuccess('No changes to save.');
        } elseif ($r['review_restarted']) {
            $this->flashSuccess('Donation ' . $r['reference'] . ' updated. Because it was already in review, verification starts again from the first stage.');
        } else {
            $this->flashSuccess('Donation ' . $r['reference'] . ' updated.');
        }
        return $this->redirect('/admin/donations/' . $id);
    }

    // ------------------------------------------------------------------ in-kind donations

    public function inkindForm(Request $request): Response
    {
        $id = (int) $request->routeParam('id', 0);
        $row = $this->inkind->find($id);
        if ($row === null) {
            $this->flashError('That in-kind donation does not exist.');
            return $this->redirect('/admin/inkind');
        }
        $items = $this->inkind->items($id);
        $this->prefill($this->inkindValues($row));
        return $this->render('admin/inkind/edit', [
            'pageTitle' => 'Edit ' . $row['public_reference'],
            'd'         => $row,
            'items'     => $items,
        ], 'layouts/admin');
    }

    public function inkindUpdate(Request $request): Response
    {
        $id = (int) $request->routeParam('id', 0);
        try {
            $r = $this->edit->updateInKind($id, $request->all(), (int) $this->auth->id());
        } catch (InvalidArgumentException $e) {
            return $this->flashFormStateTo(['form' => $e->getMessage()], $request->all(), '/admin/inkind/' . $id . '/edit');
        }
        if ($r['changed'] === []) {
            $this->flashSuccess('No changes to save.');
        } else {
            $this->flashSuccess('In-kind donation ' . $r['reference'] . ' updated.');
            if ($r['certificate_stale']) {
                $this->flashWarning('The certificate already issued was not changed. Use "Re-issue" on this page if it should show the new details.');
            }
        }
        return $this->redirect('/admin/inkind/' . $id);
    }

    // ------------------------------------------------------------------ helpers

    /**
     * Fill old() with the saved values, unless a failed save just flashed the submitted ones
     * (those win, so the staff member does not lose what they typed).
     *
     * @param array<string,mixed> $values
     */
    private function prefill(array $values): void
    {
        $flashed = $this->session->getFlash('_old', null);
        $this->session->flash('_old', is_array($flashed) && $flashed !== [] ? $flashed : $values);
    }

    /** @return array<string,string> */
    private function donationValues(array $d): array
    {
        $v = static fn (string $k): string => trim((string) ($d[$k] ?? ''));
        return [
            'amount' => number_format(((int) ($d['amount_minor'] ?? 0)) / 100, 2, '.', ''),
            'currency' => $v('currency') ?: 'PKR', 'source' => $v('source') ?: 'bank_transfer',
            'received_at' => substr($v('transaction_at') ?: $v('created_at'), 0, 10),
            'bank_reference' => $v('bank_reference'), 'bank_account_id' => $v('bank_account_id'),
            'deposited_at' => substr($v('deposited_at'), 0, 10), 'purpose_id' => $v('purpose_id') ?: '0',
            'donation_type' => $v('donation_type') ?: 'one_time', 'anonymous' => !empty($d['anonymous']) ? '1' : '',
            'donor_name' => !empty($d['anonymous']) && $v('donor_name') === 'Anonymous donor' ? '' : $v('donor_name'),
            'donor_email' => $v('donor_email'), 'donor_phone' => $v('donor_phone'), 'note' => $v('donor_message'),
            'acknowledgment_self' => (int) ($d['acknowledgment_self'] ?? 1) === 0 ? '0' : '1',
            'recipient_name' => $v('recipient_name'), 'recipient_phone' => $v('recipient_phone'),
            'recipient_email' => $v('recipient_email'), 'recipient_address' => $v('recipient_address'),
            'acknowledgment_instructions' => $v('acknowledgment_instructions'),
            'referral_enabled' => !empty($d['referral_enabled']) ? '1' : '0',
            'referrer_name' => $v('referrer_name'), 'referrer_phone' => $v('referrer_phone'), 'referral_notes' => $v('referral_notes'),
        ];
    }

    /** @return array<string,string> */
    private function inkindValues(array $d): array
    {
        $v = static fn (string $k): string => trim((string) ($d[$k] ?? ''));
        return [
            'donor_name' => $v('donor_name'), 'donor_email' => $v('donor_email'), 'donor_phone' => $v('donor_phone'),
            'donor_city' => $v('donor_city'), 'donor_address' => $v('donor_address'),
            'handover_method' => $v('handover_method') ?: 'drop_off', 'handover_date' => substr($v('handover_date'), 0, 10),
            'pickup_address' => $v('pickup_address'), 'description' => $v('description'),
            'acknowledgment_self' => (int) ($d['acknowledgment_self'] ?? 1) === 0 ? '0' : '1',
            'recipient_name' => $v('recipient_name'), 'recipient_phone' => $v('recipient_phone'),
            'recipient_email' => $v('recipient_email'), 'recipient_address' => $v('recipient_address'),
            'acknowledgment_instructions' => $v('acknowledgment_instructions'),
            'referral_enabled' => !empty($d['referral_enabled']) ? '1' : '0',
            'referrer_name' => $v('referrer_name'), 'referrer_phone' => $v('referrer_phone'), 'referral_notes' => $v('referral_notes'),
        ];
    }
}
