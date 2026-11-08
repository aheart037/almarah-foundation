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
use Throwable;

/** Certificate download (donations.view) and recovery actions (donations.verify); routes enforce permissions + CSRF. */
final class DonationCertificateController extends Controller
{
    public function __construct(
        View $view, Session $session, Csrf $csrf, AuthService $auth,
        private DonationCertificateService $certificates, private DonationRepository $donations, private AuditService $audit
    ) {
        parent::__construct($view, $session, $csrf, $auth);
    }

    public function download(Request $request): Response
    {
        $id = (int) $request->routeParam('id', 0);
        $cert = $this->certificates->find($id);
        $path = $cert && $cert['status'] === 'generated' ? $this->certificates->absolutePath($cert) : null;
        if (!$path) {
            abort(404, 'No certificate on file.');
        }
        $this->audit->log('donation.certificate.downloaded', 'donation', $id, ['certificate' => $cert['certificate_number']], (int) $this->auth->id());
        return Response::file($path, (string) $cert['certificate_number'] . '.pdf', 'application/pdf')
            ->withHeader('X-Content-Type-Options', 'nosniff');
    }

    /** Create the certificate for a verified donation when automatic generation failed. */
    public function generate(Request $request): Response
    {
        $id = (int) $request->routeParam('id', 0);
        $result = $this->certificates->issue($id, (int) $this->auth->id());
        $result['ok'] ? $this->flashSuccess('Certificate generated.') : $this->flashError('Certificate could not be generated: ' . ($result['message'] ?: 'unknown error'));
        return $this->redirect('/admin/donations/' . $id);
    }

    public function retryEmail(Request $request): Response
    {
        $id = (int) $request->routeParam('id', 0);
        $cert = $this->certificates->find($id);
        if (!$cert || $cert['status'] !== 'generated') {
            $this->flashError('Generate the certificate first.');
            return $this->redirect('/admin/donations/' . $id);
        }
        try {
            $status = $this->certificates->sendEmail((int) $cert['id'], true);
        } catch (Throwable) {
            $status = 'failed';
        }
        match ($status) {
            'sent' => $this->flashSuccess('Certificate emailed to the donor.'),
            'queued' => $this->flashSuccess('Certificate email queued.'),
            'skipped' => $this->flashError('This donation has no valid donor email.'),
            default => $this->flashError('The email could not be sent. See the status below and retry later.'),
        };
        return $this->redirect('/admin/donations/' . $id);
    }
}
