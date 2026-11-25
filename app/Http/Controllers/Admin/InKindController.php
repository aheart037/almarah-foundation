<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Services\AuthService;
use App\Services\DonorWhatsAppService;
use App\Services\InKindCertificateService;
use App\Services\InKindDonationService;
use App\Services\RecordDeletionService;
use InvalidArgumentException;
use Throwable;

/**
 * Finance/admin screens for in-kind (goods) donations. Routes enforce permissions + CSRF:
 * viewing = inkind.view or inkind.verify; every action = inkind.verify.
 */
final class InKindController extends Controller
{
    public function __construct(
        View $view, Session $session, Csrf $csrf, AuthService $auth,
        private InKindDonationService $inkind, private InKindCertificateService $certificates, private AuditService $audit,
        private RecordDeletionService $deletion, private DonorWhatsAppService $donorWhatsApp
    ) {
        parent::__construct($view, $session, $csrf, $auth);
    }

    public function index(Request $request): Response
    {
        $filters = [
            'q' => trim((string) $request->input('q', '')), 'status' => (string) $request->input('status', ''),
            'date_from' => (string) $request->input('date_from', ''), 'date_to' => (string) $request->input('date_to', ''),
        ];
        try {
            $result = $this->inkind->search($filters, max(1, (int) $request->input('page', 1)));
        } catch (\PDOException $e) {
            if (str_contains($e->getMessage(), 'inkind_donations') || str_contains($e->getMessage(), '42S02')) {
                abort(503, 'The in-kind donation tables are not in the database yet. In phpMyAdmin run setup/4-run-fourth-0022-in-kind-donations.sql, then setup/5-run-fifth-0023-inkind-permissions.sql, and reload this page.');
            }
            throw $e;
        }
        return $this->render('admin/inkind/index', [
            'pageTitle' => 'In-kind donations', 'filters' => $filters, 'result' => $result,
            'page' => $result['page'], 'lastPage' => $result['lastPage'], 'total' => $result['total'],
        ], 'layouts/admin');
    }

    public function show(Request $request): Response
    {
        $id = (int) $request->routeParam('id', 0);
        $row = $this->inkind->find($id);
        if (!$row) {
            $this->flashError('In-kind donation not found.');
            return $this->redirect('/admin/inkind');
        }
        return $this->render('admin/inkind/show', [
            'pageTitle' => 'In-kind donation ' . $row['public_reference'], 'd' => $row,
            'items' => $this->inkind->items($id), 'events' => $this->inkind->events($id),
            'certs' => array_map(fn (array $c): array => $this->certificates->syncEmailStatus($c), $this->certificates->history($id)),
            'photoCount' => count($this->inkind->photos($row)),
            'certName' => InKindDonationService::certificateName($row),
        ], 'layouts/admin');
    }

    /** Verify / reject / reopen. Verifying also generates and emails the certificate. */
    public function decide(Request $request): Response
    {
        $id = (int) $request->routeParam('id', 0);
        $decision = (string) $request->input('decision', '');
        $actor = (int) $this->auth->id();
        try {
            $this->inkind->decide($id, $actor, $decision, (string) $request->input('note', ''));
        } catch (InvalidArgumentException $e) {
            $this->flashError($e->getMessage());
            return $this->redirect('/admin/inkind/' . $id);
        }
        if ($decision === 'approve') {
            $this->issueAndSend($id, $actor, false);
        } else {
            $this->flashSuccess($decision === 'reject' ? 'Donation rejected.' : 'Donation reopened and set back to pending.');
        }
        return $this->redirect('/admin/inkind/' . $id);
    }

    /** Generate (or re-issue as a new version) the certificate for a verified donation. */
    public function generate(Request $request): Response
    {
        $id = (int) $request->routeParam('id', 0);
        $this->issueAndSend($id, (int) $this->auth->id(), (string) $request->input('reissue', '') === '1');
        return $this->redirect('/admin/inkind/' . $id);
    }

    public function resend(Request $request): Response
    {
        $certId = (int) $request->routeParam('id', 0);
        $cert = $this->certificates->find($certId);
        if (!$cert || $cert['status'] !== 'generated') {
            $this->flashError('That certificate does not exist.');
            return $this->redirect('/admin/inkind');
        }
        try {
            $status = $this->certificates->sendEmail($certId, true);
        } catch (Throwable) {
            $status = 'failed';
        }
        $this->flashEmail($status);
        return $this->redirect('/admin/inkind/' . (int) $cert['inkind_id']);
    }

    /** Sends this certificate PDF (the same file as "Download PDF") to the donor's WhatsApp number. */
    public function whatsapp(Request $request): Response
    {
        $r = $this->donorWhatsApp->sendInKind((int) $request->routeParam('id', 0), (int) $this->auth->id());
        $r['ok'] ? $this->flashSuccess($r['message']) : $this->flashError($r['message']);
        return $this->redirect($r['inkind_id'] > 0 ? '/admin/inkind/' . $r['inkind_id'] : '/admin/inkind');
    }

    public function download(Request $request): Response
    {
        $cert = $this->certificates->find((int) $request->routeParam('id', 0));
        $path = $cert && $cert['status'] === 'generated' ? $this->certificates->absolutePath($cert) : null;
        if (!$path) {
            abort(404, 'No certificate on file.');
        }
        $this->audit->log('inkind.certificate.downloaded', 'inkind_donation', (int) $cert['inkind_id'], ['certificate' => $cert['certificate_number']], (int) $this->auth->id());
        return Response::file($path, $cert['certificate_number'] . '-v' . $cert['version'] . '.pdf', 'application/pdf')
            ->withHeader('X-Content-Type-Options', 'nosniff');
    }

    public function photo(Request $request): Response
    {
        $row = $this->inkind->find((int) $request->routeParam('id', 0));
        $path = $row ? $this->inkind->photoPath($row, (int) $request->routeParam('n', 0)) : null;
        $mime = $path ? (['png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg'][strtolower(pathinfo($path, PATHINFO_EXTENSION))] ?? null) : null;
        if (!$path || !$mime) {
            abort(404, 'No photo on file.');
        }
        $this->audit->log('inkind.photo.viewed', 'inkind_donation', (int) $row['id'], [], (int) $this->auth->id());
        return Response::file($path, 'goods-' . preg_replace('/[^A-Za-z0-9-]/', '', (string) $row['public_reference']) . '.' . pathinfo($path, PATHINFO_EXTENSION), $mime)
            ->withHeader('Content-Disposition', 'inline')
            ->withHeader('X-Content-Type-Options', 'nosniff')
            ->withHeader('Content-Security-Policy', "default-src 'none'; img-src 'self'");
    }

    /** Permanently delete one pending/rejected in-kind submission (verified ones keep their certificate). */
    public function destroy(Request $request): Response
    {
        $id = (int) $request->routeParam('id', 0);
        try {
            $reason = RecordDeletionService::cleanReason($request->input('reason', ''));
        } catch (InvalidArgumentException $e) {
            $this->flashError($e->getMessage());
            return $this->redirect('/admin/inkind/' . $id);
        }
        $result = $this->deletion->deleteInKind($id, (int) $this->auth->id(), $reason);
        if ($result['ok']) {
            $this->flashSuccess($result['message']);
            return $this->redirect('/admin/inkind');
        }
        $this->flashError($result['message']);
        return $this->redirect('/admin/inkind/' . $id);
    }

    public function bulkDestroy(Request $request): Response
    {
        $ids = RecordDeletionService::cleanIds($request->input('ids', []));
        if ($ids === []) {
            $this->flashError('Tick at least one donation first.');
            return $this->redirect('/admin/inkind');
        }
        try {
            $reason = RecordDeletionService::cleanReason($request->input('reason', ''));
        } catch (InvalidArgumentException $e) {
            $this->flashError($e->getMessage());
            return $this->redirect('/admin/inkind');
        }
        $r = $this->deletion->bulk($ids, (int) $this->auth->id(), fn (int $id, int $actor): array => $this->deletion->deleteInKind($id, $actor, $reason));
        if ($r['deleted'] > 0) {
            $this->flashSuccess($r['deleted'] . ' in-kind donation' . ($r['deleted'] === 1 ? '' : 's') . ' deleted.');
        }
        if ($r['skipped'] !== []) {
            $this->flashWarning(count($r['skipped']) . ' not deleted: ' . implode(' | ', array_slice($r['skipped'], 0, 5)) . (count($r['skipped']) > 5 ? ' …' : ''));
        }
        return $this->redirect('/admin/inkind');
    }

    // ------------------------------------------------------------------ helpers

    private function issueAndSend(int $id, int $actor, bool $newVersion): void
    {
        try {
            $res = $this->certificates->generate($id, $actor, $newVersion);
        } catch (Throwable $e) {
            $this->flashError('Certificate could not be generated: ' . $e->getMessage());
            return;
        }
        if (!$res['ok'] || !$res['row']) {
            $this->flashWarning('The donation is verified, but the certificate could not be generated: ' . $res['message'] . ' You can retry from the certificate section.');
            return;
        }
        try {
            $status = $this->certificates->sendEmail((int) $res['row']['id'], false);
        } catch (Throwable) {
            $status = 'failed';
        }
        $this->flashEmail($status, true);
    }

    private function flashEmail(string $status, bool $afterGenerate = false): void
    {
        $prefix = $afterGenerate ? 'Verified. Certificate generated. ' : '';
        match ($status) {
            'sent'    => $this->flashSuccess($prefix . 'The thank-you certificate was emailed to the donor.'),
            'queued'  => $this->flashSuccess($prefix . 'The thank-you email is queued and will be sent shortly.'),
            'skipped' => $this->flashWarning($prefix . 'No valid donor email is on file, so nothing was sent.'),
            default   => $this->flashWarning($prefix . 'The email could not be sent. Use "Resend email" in the certificate section to retry.'),
        };
    }
}
