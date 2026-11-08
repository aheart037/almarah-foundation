<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Http\Controllers\Controller;
use App\Repositories\DonationRepository;
use App\Services\AuditService;
use App\Services\AuthService;
use App\Services\DonationCertificateService;
use App\Services\DonationReviewService;
use App\Services\UploadService;
use InvalidArgumentException;
use RuntimeException;

/** Finance verification / rejection and secure slip viewing. */
final class DonationReviewController extends Controller
{
    public function __construct(
        View $view, Session $session, Csrf $csrf, AuthService $auth,
        private DonationReviewService $review, private DonationRepository $donations,
        private UploadService $uploads, private AuditService $audit, private DonationCertificateService $certificates
    ) {
        parent::__construct($view, $session, $csrf, $auth);
    }

    /** Route middleware enforces donations.verify + CSRF; the service re-checks stage role, locks the row and blocks double decisions. */
    public function decide(Request $request): Response
    {
        $id = (int) $request->routeParam('id', 0);
        $to = '/admin/donations/' . $id;
        try {
            $final = $this->review->decide($id, (int) $this->auth->id(), $this->auth->roles(), (string) $request->input('decision', ''), trim((string) $request->input('comment', '')));
        } catch (InvalidArgumentException | RuntimeException $e) {
            $this->flashError($e->getMessage());
            return $this->redirect($to);
        }
        if ($final) {
            // Certificate + email run after the verification is committed; their failure never undoes it.
            $c = $this->certificates->issue($id, (int) $this->auth->id());
            $this->flashSuccess('Donation verified.' . ($c['ok'] ? ' Certificate issued.' : ' The certificate could not be generated yet; see the Certificate panel.'));
            return $this->redirect($to);
        }
        $this->flashSuccess('Decision recorded.');
        return $this->redirect($to);
    }

    /** Streams the stored slip to authorised staff only; every view is audit-logged. */
    public function proof(Request $request): Response
    {
        $id = (int) $request->routeParam('id', 0);
        $d = $this->donations->find($id);
        $path = $d ? $this->uploads->privatePath((string) ($d['payment_proof_path'] ?? '')) : null;
        if (!$d || !$path) {
            abort(404, 'No payment slip on file.');
        }
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = ['png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg'][$ext] ?? null;
        if ($mime === null) {
            abort(404, 'Unsupported slip type.');
        }
        $this->audit->log('donation.proof.viewed', 'donation', $id, [], (int) $this->auth->id());
        return Response::file($path, 'slip-' . preg_replace('/[^A-Za-z0-9-]/', '', (string) $d['public_reference']) . '.' . $ext, $mime)
            ->withHeader('Content-Disposition', 'inline')
            ->withHeader('X-Content-Type-Options', 'nosniff')
            ->withHeader('Content-Security-Policy', "default-src 'none'; img-src 'self'");
    }
}
