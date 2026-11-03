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
use App\Services\AuditService;
use App\Services\AuthService;
use App\Services\UploadService;

/**
 * Website form submissions: contact messages, newsletter sign-ups, volunteer
 * applications, job applications and fundraising enquiries.
 *
 * Each "box" is a table with a status column and admin notes. Staff can
 * filter, read, change status, add notes and export CSV. Personal data is
 * only ever shown to roles holding the box's permission.
 */
final class InboxController extends Controller
{
    private const PER_PAGE = 40;

    public function __construct(
        View $view,
        Session $session,
        Csrf $csrf,
        AuthService $auth,
        private Database $db,
        private UploadService $uploads,
        private AuditService $audit
    ) {
        parent::__construct($view, $session, $csrf, $auth);
    }

    /** @return array<string,array<string,mixed>> */
    public static function boxes(): array
    {
        return [
            'contact' => [
                'table'      => 'contact_messages',
                'label'      => 'Contact messages',
                'permission' => 'forms.view',
                'statuses'   => ['new' => 'New', 'read' => 'Read', 'replied' => 'Replied', 'archived' => 'Archived', 'spam' => 'Spam'],
                'open'       => ['new'],
                'list'       => ['name' => 'From', 'subject' => 'Subject', 'email' => 'Email', 'status' => 'Status', 'created_at' => 'Received'],
                'search'     => ['name', 'email', 'subject', 'message'],
                'detail'     => ['name' => 'Name', 'email' => 'Email', 'phone' => 'Phone', 'subject' => 'Subject', 'message' => 'Message', 'ip_address' => 'IP address', 'created_at' => 'Received'],
                'long'       => ['message'],
                'title'      => 'subject',
            ],
            'newsletter' => [
                'table'      => 'newsletter_subscribers',
                'label'      => 'Newsletter subscribers',
                'permission' => 'forms.view',
                'statuses'   => ['subscribed' => 'Subscribed', 'pending' => 'Pending', 'unsubscribed' => 'Unsubscribed', 'bounced' => 'Bounced'],
                'open'       => ['subscribed'],
                'list'       => ['email' => 'Email', 'name' => 'Name', 'source' => 'Source', 'status' => 'Status', 'created_at' => 'Joined'],
                'search'     => ['email', 'name'],
                'detail'     => ['email' => 'Email', 'name' => 'Name', 'source' => 'Source', 'ip_address' => 'IP address', 'created_at' => 'Joined', 'unsubscribed_at' => 'Unsubscribed'],
                'long'       => [],
                'title'      => 'email',
                'no_notes'   => true,
            ],
            'volunteers' => [
                'table'      => 'volunteers',
                'label'      => 'Volunteer applications',
                'permission' => 'volunteers.manage',
                'statuses'   => ['pending' => 'Pending review', 'approved' => 'Approved', 'active' => 'Active volunteer', 'inactive' => 'Inactive', 'rejected' => 'Not suitable'],
                'open'       => ['pending'],
                'list'       => ['first_name' => 'Name', 'city' => 'City', 'skills' => 'Skills', 'availability' => 'Availability', 'status' => 'Status', 'created_at' => 'Applied'],
                'search'     => ['first_name', 'last_name', 'email', 'phone', 'city', 'skills'],
                'detail'     => ['first_name' => 'First name', 'last_name' => 'Last name', 'email' => 'Email', 'phone' => 'Phone', 'alt_phone' => 'Alternate phone', 'date_of_birth' => 'Date of birth', 'gender' => 'Gender', 'cnic' => 'CNIC', 'address_line' => 'Address', 'city' => 'City', 'state' => 'Province / state', 'country' => 'Country', 'occupation' => 'Occupation', 'organisation' => 'Organisation', 'skills' => 'Skills', 'interests_json' => 'Interests', 'availability' => 'Availability', 'previous_experience' => 'Previous experience', 'motivation' => 'Motivation', 'emergency_contact' => 'Emergency contact', 'created_at' => 'Applied'],
                'long'       => ['previous_experience', 'motivation', 'address_line'],
                'title'      => 'first_name',
                'reviewed'   => true,
            ],
            'careers' => [
                'table'      => 'career_applications',
                'label'      => 'Job applications',
                'permission' => 'volunteers.manage',
                'statuses'   => ['new' => 'New', 'shortlisted' => 'Shortlisted', 'interview' => 'Interview', 'hired' => 'Hired', 'rejected' => 'Rejected', 'archived' => 'Archived'],
                'open'       => ['new'],
                'list'       => ['name' => 'Name', 'position' => 'Position', 'city' => 'City', 'status' => 'Status', 'created_at' => 'Applied'],
                'search'     => ['name', 'email', 'position', 'city'],
                'detail'     => ['name' => 'Name', 'email' => 'Email', 'phone' => 'Phone', 'city' => 'City', 'position' => 'Position', 'linkedin_url' => 'LinkedIn', 'cover_letter' => 'Cover letter', 'resume_original_name' => 'CV', 'created_at' => 'Applied'],
                'long'       => ['cover_letter'],
                'title'      => 'name',
                'resume'     => true,
            ],
            'fundraising' => [
                'table'      => 'fundraising_applications',
                'label'      => 'Fundraising enquiries',
                'permission' => 'forms.view',
                'statuses'   => ['new' => 'New', 'contacted' => 'Contacted', 'approved' => 'Approved', 'rejected' => 'Declined', 'archived' => 'Archived'],
                'open'       => ['new'],
                'list'       => ['name' => 'Name', 'campaign_title' => 'Campaign', 'goal_amount' => 'Goal', 'status' => 'Status', 'created_at' => 'Received'],
                'search'     => ['name', 'email', 'campaign_title', 'organisation'],
                'detail'     => ['name' => 'Name', 'email' => 'Email', 'phone' => 'Phone', 'organisation' => 'Organisation', 'campaign_title' => 'Campaign title', 'cause' => 'Cause', 'goal_amount' => 'Goal (PKR)', 'description' => 'Description', 'created_at' => 'Received'],
                'long'       => ['description'],
                'title'      => 'campaign_title',
            ],
        ];
    }

    /** Unread counts for the sidebar badge (only boxes the user may see). */
    public static function openCounts(Database $db, AuthService $auth): array
    {
        $counts = [];
        foreach (self::boxes() as $key => $box) {
            if (!$auth->can($box['permission'])) {
                continue;
            }
            $in = "'" . implode("','", $box['open']) . "'";
            try {
                $counts[$key] = $db->int("SELECT COUNT(*) FROM `{$box['table']}` WHERE status IN ({$in})");
            } catch (\Throwable) {
                $counts[$key] = 0;
            }
        }
        return $counts;
    }

    public function index(Request $request): Response
    {
        $box = $this->box($request);
        $key = $box['key'];

        $q = trim($request->string('q'));
        $status = $request->string('status');
        $where = [];
        $params = [];

        if ($status !== '' && isset($box['statuses'][$status])) {
            $where[] = 'status = :status';
            $params['status'] = $status;
        }
        if ($q !== '') {
            $parts = [];
            foreach ($box['search'] as $i => $column) {
                $parts[] = "`{$column}` LIKE :q{$i}";
                $params["q{$i}"] = '%' . $q . '%';
            }
            $where[] = '(' . implode(' OR ', $parts) . ')';
        }
        $sqlWhere = $where !== [] ? ' WHERE ' . implode(' AND ', $where) : '';

        $total = $this->db->int("SELECT COUNT(*) FROM `{$box['table']}`{$sqlWhere}", $params);
        $page = max(1, $request->int('page', 1));
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        $page = min($page, $lastPage);
        $offset = ($page - 1) * self::PER_PAGE;
        $rows = $this->db->select(
            "SELECT * FROM `{$box['table']}`{$sqlWhere} ORDER BY id DESC LIMIT " . self::PER_PAGE . " OFFSET {$offset}",
            $params
        );

        $statusCounts = [];
        foreach ($this->db->select("SELECT status, COUNT(*) AS n FROM `{$box['table']}` GROUP BY status") as $r) {
            $statusCounts[(string) $r['status']] = (int) $r['n'];
        }

        return $this->render('admin/inbox/index', [
            'pageTitle'    => $box['label'],
            'box'          => $box,
            'boxes'        => $this->visibleBoxes(),
            'openCounts'   => self::openCounts($this->db, $this->auth),
            'rows'         => $rows,
            'q'            => $q,
            'status'       => $status,
            'statusCounts' => $statusCounts,
            'page'         => $page,
            'lastPage'     => $lastPage,
            'total'        => $total,
            'query'        => array_filter(['q' => $q, 'status' => $status]),
        ], 'layouts/admin');
    }

    public function show(Request $request): Response
    {
        $box = $this->box($request);
        $row = $this->find($box, (int) $request->routeParam('id', 0));

        // Opening a brand-new item marks it as read (contact box only).
        if ($box['key'] === 'contact' && $row['status'] === 'new') {
            $this->db->update($box['table'], ['status' => 'read'], 'id = :id', ['id' => (int) $row['id']]);
            $row['status'] = 'read';
        }

        $reviewer = null;
        if (!empty($box['reviewed']) && !empty($row['reviewed_by'])) {
            $reviewer = $this->db->selectOne('SELECT first_name, last_name FROM users WHERE id = :id', ['id' => (int) $row['reviewed_by']]);
        }

        return $this->render('admin/inbox/show', [
            'pageTitle' => $box['label'],
            'box'       => $box,
            'row'       => $row,
            'reviewer'  => $reviewer,
        ], 'layouts/admin');
    }

    public function update(Request $request): Response
    {
        $box = $this->box($request);
        $row = $this->find($box, (int) $request->routeParam('id', 0));

        $status = $request->string('status');
        if (!isset($box['statuses'][$status])) {
            $this->flashError('Unknown status.');
            return $this->back('/admin/inbox/' . $box['key']);
        }
        $data = ['status' => $status];
        if (empty($box['no_notes'])) {
            $notes = trim($request->string('admin_notes'));
            $data['admin_notes'] = $notes !== '' ? mb_substr($notes, 0, 10000) : null;
        }
        if (!empty($box['reviewed']) && $status !== $row['status']) {
            $data['reviewed_by'] = (int) $this->auth->id();
            $data['reviewed_at'] = gmdate('Y-m-d H:i:s');
        }
        if ($box['key'] === 'newsletter') {
            $data['unsubscribed_at'] = $status === 'unsubscribed' ? gmdate('Y-m-d H:i:s') : null;
        }
        $this->db->update($box['table'], $data, 'id = :id', ['id' => (int) $row['id']]);

        $this->audit->log('inbox.' . $box['key'] . '.updated', $box['table'], (int) $row['id'], ['status' => $status], (int) $this->auth->id());
        $this->flashSuccess('Saved.');
        return $this->redirect('/admin/inbox/' . $box['key'] . '/' . (int) $row['id']);
    }

    public function destroy(Request $request): Response
    {
        $box = $this->box($request);
        $row = $this->find($box, (int) $request->routeParam('id', 0));
        if (!empty($box['resume']) && !empty($row['resume_path'])) {
            $path = $this->uploads->privatePath((string) $row['resume_path']);
            if ($path !== null && is_file($path)) {
                @unlink($path);
            }
        }
        $this->db->delete($box['table'], 'id = :id', ['id' => (int) $row['id']]);
        $this->audit->log('inbox.' . $box['key'] . '.deleted', $box['table'], (int) $row['id'], [], (int) $this->auth->id());
        $this->flashSuccess('Deleted permanently.');
        return $this->redirect('/admin/inbox/' . $box['key']);
    }

    /** Serve a CV from private storage (never web-accessible directly). */
    public function resume(Request $request): Response
    {
        $box = $this->box($request);
        if (empty($box['resume'])) {
            abort(404);
        }
        $row = $this->find($box, (int) $request->routeParam('id', 0));
        $path = $this->uploads->privatePath((string) ($row['resume_path'] ?? ''));
        if ($path === null || !is_file($path)) {
            abort(404, 'The CV file is no longer available.');
        }
        $this->audit->log('inbox.careers.resume_downloaded', $box['table'], (int) $row['id'], [], (int) $this->auth->id());

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = ['pdf' => 'application/pdf', 'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'][$ext] ?? 'application/octet-stream';
        $name = preg_replace('/[^A-Za-z0-9._-]/', '_', (string) ($row['resume_original_name'] ?: 'cv.' . $ext)) ?: 'cv.' . $ext;

        return new Response((string) file_get_contents($path), 200, [
            'Content-Type'           => $mime,
            'Content-Disposition'    => 'attachment; filename="' . $name . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control'          => 'private, no-store',
        ]);
    }

    public function export(Request $request): Response
    {
        $box = $this->box($request);
        $status = $request->string('status');
        $params = [];
        $sqlWhere = '';
        if ($status !== '' && isset($box['statuses'][$status])) {
            $sqlWhere = ' WHERE status = :status';
            $params['status'] = $status;
        }
        $rows = $this->db->select("SELECT * FROM `{$box['table']}`{$sqlWhere} ORDER BY id DESC LIMIT 20000", $params);

        $skip = ['fingerprint', 'confirm_token_hash', 'unsubscribe_token_hash', 'user_agent', 'resume_path'];
        $out = fopen('php://temp', 'w+');
        if ($rows !== []) {
            $columns = array_values(array_diff(array_keys($rows[0]), $skip));
            fputcsv($out, $columns);
            foreach ($rows as $row) {
                $line = [];
                foreach ($columns as $column) {
                    $value = (string) ($row[$column] ?? '');
                    // Neutralise spreadsheet formula injection.
                    if ($value !== '' && in_array($value[0], ['=', '+', '-', '@'], true)) {
                        $value = "'" . $value;
                    }
                    $line[] = $value;
                }
                fputcsv($out, $line);
            }
        }
        rewind($out);
        $csv = (string) stream_get_contents($out);
        fclose($out);

        $this->audit->log('inbox.' . $box['key'] . '.exported', $box['table'], null, ['rows' => count($rows), 'status' => $status], (int) $this->auth->id());

        return new Response("\xEF\xBB\xBF" . $csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $box['key'] . '-' . gmdate('Ymd') . '.csv"',
            'Cache-Control'       => 'private, no-store',
        ]);
    }

    // ------------------------------------------------------------- internals

    /** @return array<string,mixed> */
    private function box(Request $request): array
    {
        $key = (string) $request->routeParam('box', '');
        $boxes = self::boxes();
        if (!isset($boxes[$key])) {
            abort(404, 'Unknown inbox.');
        }
        $box = $boxes[$key] + ['key' => $key];
        if (!$this->auth->can((string) $box['permission'])) {
            abort(403, 'Your role does not include permission for ' . $box['label'] . '.');
        }
        return $box;
    }

    /** @return array<string,array<string,mixed>> */
    private function visibleBoxes(): array
    {
        $visible = [];
        foreach (self::boxes() as $key => $box) {
            if ($this->auth->can((string) $box['permission'])) {
                $visible[$key] = $box + ['key' => $key];
            }
        }
        return $visible;
    }

    /** @return array<string,mixed> */
    private function find(array $box, int $id): array
    {
        $row = $id > 0 ? $this->db->selectOne("SELECT * FROM `{$box['table']}` WHERE id = :id", ['id' => $id]) : null;
        if ($row === null) {
            abort(404, 'Not found.');
        }
        return $row;
    }
}
