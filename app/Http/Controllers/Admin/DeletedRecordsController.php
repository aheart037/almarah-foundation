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
use App\Services\AuthService;

/**
 * Read-only list of permanently deleted records with who deleted them, when, and the reason given.
 * Each person sees only the kinds they are allowed to delete (donations.delete, inkind.delete, forms.delete).
 */
final class DeletedRecordsController extends Controller
{
    private const PER_PAGE = 50;

    public const TYPES = [
        'donation'    => ['Donation', 'donations.delete'],
        'inkind'      => ['In-kind donation', 'inkind.delete'],
        'contact'     => ['Contact message', 'forms.delete'],
        'newsletter'  => ['Newsletter subscriber', 'forms.delete'],
        'volunteers'  => ['Volunteer application', 'forms.delete'],
        'careers'     => ['Job application', 'forms.delete'],
        'fundraising' => ['Fundraising enquiry', 'forms.delete'],
    ];

    public function __construct(View $view, Session $session, Csrf $csrf, AuthService $auth, private Database $db)
    {
        parent::__construct($view, $session, $csrf, $auth);
    }

    public function index(Request $request): Response
    {
        $allowed = [];
        foreach (self::TYPES as $key => [$label, $permission]) {
            if ($this->auth->can($permission)) {
                $allowed[$key] = $label;
            }
        }
        $q = trim((string) $request->input('q', ''));
        $type = (string) $request->input('type', '');
        if ($type !== '' && !isset($allowed[$type])) {
            $type = '';
        }

        $params = [];
        $in = [];
        foreach (array_keys($type !== '' ? [$type => 1] : $allowed) as $i => $key) {
            $in[] = ':t' . $i;
            $params['t' . $i] = $key;
        }
        $where = $in === [] ? ['1 = 0'] : ['d.record_type IN (' . implode(',', $in) . ')'];
        if ($q !== '') {
            $where[] = '(d.reference LIKE :q1 OR d.summary LIKE :q2 OR d.reason LIKE :q3 OR u.email LIKE :q4)';
            foreach ([1, 2, 3, 4] as $n) {
                $params['q' . $n] = '%' . $q . '%';
            }
        }
        $clause = implode(' AND ', $where);
        $page = max(1, (int) $request->input('page', 1));
        $offset = ($page - 1) * self::PER_PAGE;
        $total = $this->db->int("SELECT COUNT(*) FROM deleted_records d LEFT JOIN users u ON u.id = d.deleted_by WHERE {$clause}", $params);
        $rows = $this->db->select(
            "SELECT d.*, u.email AS deleted_by_email FROM deleted_records d LEFT JOIN users u ON u.id = d.deleted_by
              WHERE {$clause} ORDER BY d.id DESC LIMIT " . self::PER_PAGE . " OFFSET {$offset}",
            $params
        );

        return $this->render('admin/deleted-records', [
            'pageTitle' => 'Deleted records',
            'rows'      => $rows,
            'total'     => $total,
            'page'      => $page,
            'lastPage'  => max(1, (int) ceil($total / self::PER_PAGE)),
            'filters'   => ['q' => $q, 'type' => $type],
            'types'     => $allowed,
        ], 'layouts/admin');
    }
}
