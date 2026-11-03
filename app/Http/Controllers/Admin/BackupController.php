<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Services\BackupService;
use Throwable;

/** Backups page: readiness, run, download, delete. Requires backups.manage. */
final class BackupController extends Controller
{
    public function __construct(
        View $view,
        Session $session,
        Csrf $csrf,
        AuthService $auth,
        private BackupService $backups
    ) {
        parent::__construct($view, $session, $csrf, $auth);
    }

    public function index(Request $request): Response
    {
        return $this->render('admin/backups/index', [
            'pageTitle' => 'Backups',
            'status'    => $this->backups->status(),
            'logs'      => $this->backups->logs(30),
        ], 'layouts/admin');
    }

    public function run(Request $request): Response
    {
        $kind = (string) $request->input('kind', 'database');
        try {
            $result = $this->backups->run($kind, (int) $this->auth->id(), 'admin');
            $this->flashSuccess(sprintf('%s backup created (%s).', ucfirst($kind), human_size($result['size'])));
        } catch (Throwable $e) {
            $this->flashError('Backup failed: ' . $e->getMessage());
        }
        return $this->redirect('/admin/backups');
    }

    public function download(Request $request): Response
    {
        $log = $this->backups->find((int) $request->routeParam('id', 0));
        $file = $log ? $this->backups->fileFor($log) : null;
        if ($log === null || $file === null) {
            $this->flashError('That backup file is no longer on the server.');
            return $this->redirect('/admin/backups');
        }
        $this->auth->id() && app(\App\Services\AuditService::class)->log('backups.downloaded', 'backup', (int) $log['id'], ['file' => $log['file_name']], (int) $this->auth->id());

        return Response::file($file, basename((string) $log['file_name']));
    }

    public function delete(Request $request): Response
    {
        $ok = $this->backups->delete((int) $request->routeParam('id', 0), (int) $this->auth->id());
        $ok ? $this->flashSuccess('Backup deleted.') : $this->flashError('Backup not found.');
        return $this->redirect('/admin/backups');
    }
}
