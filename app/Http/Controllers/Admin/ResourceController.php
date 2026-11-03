<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Admin\ResourceRegistry;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Str;
use App\Core\Validator;
use App\Core\View;
use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Services\AuthService;
use App\Services\UploadService;
use InvalidArgumentException;
use RuntimeException;

/**
 * One controller for every CMS table described in ResourceRegistry:
 * list (search, filter, paginate), create, edit, delete, reorder.
 *
 * Column names, options and validation all come from the registry — nothing
 * from the request decides what is written. Permission is checked per
 * resource here as well as by the route middleware, so a content manager can
 * never reach bank accounts by guessing a URL.
 */
final class ResourceController extends Controller
{
    private const PER_PAGE = 30;

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

    // ---------------------------------------------------------------- list

    public function index(Request $request): Response
    {
        $def = $this->definition($request);
        $table = $def['table'];

        $q = trim($request->string('q'));
        $where = [];
        $params = [];

        if ($q !== '' && !empty($def['search'])) {
            $parts = [];
            foreach ($def['search'] as $i => $column) {
                $parts[] = "`{$column}` LIKE :q{$i}";
                $params["q{$i}"] = '%' . $q . '%';
            }
            $where[] = '(' . implode(' OR ', $parts) . ')';
        }

        $activeFilters = [];
        foreach ($def['filters'] ?? [] as $column => $options) {
            $value = $request->string($column);
            if ($value !== '' && array_key_exists($value, $options)) {
                $where[] = "`{$column}` = :f_{$column}";
                $params["f_{$column}"] = $value;
                $activeFilters[$column] = $value;
            }
        }

        $sqlWhere = $where !== [] ? ' WHERE ' . implode(' AND ', $where) : '';
        $total = $this->db->int("SELECT COUNT(*) FROM `{$table}`{$sqlWhere}", $params);
        $page = max(1, $request->int('page', 1));
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        $page = min($page, $lastPage);
        $offset = ($page - 1) * self::PER_PAGE;

        $rows = $this->db->select(
            "SELECT * FROM `{$table}`{$sqlWhere} ORDER BY {$def['order']} LIMIT " . self::PER_PAGE . " OFFSET {$offset}",
            $params
        );

        return $this->render('admin/resources/index', [
            'pageTitle' => $def['label'],
            'def'       => $def,
            'rows'      => $rows,
            'q'         => $q,
            'filters'   => $activeFilters,
            'page'      => $page,
            'lastPage'  => $lastPage,
            'total'     => $total,
            'query'     => array_filter(['q' => $q] + $activeFilters),
        ], 'layouts/admin');
    }

    // --------------------------------------------------------------- forms

    public function create(Request $request): Response
    {
        $def = $this->definition($request);
        // Query-string prefill lets other screens deep-link ("add redirect for /old").
        $prefill = [];
        foreach ($request->all() as $key => $value) {
            if (isset($def['fields'][$key]) && is_string($value)) {
                $prefill[$key] = mb_substr($value, 0, 500);
            }
        }
        return $this->render('admin/resources/form', [
            'pageTitle' => 'New ' . $def['singular'],
            'def'       => $def,
            'row'       => null,
            'revisions' => [],
            'prefill'   => $prefill,
        ], 'layouts/admin');
    }

    public function edit(Request $request): Response
    {
        $def = $this->definition($request);
        $row = $this->find($def, (int) $request->routeParam('id', 0));

        $revisions = [];
        if (!empty($def['revisions'])) {
            $revisions = $this->db->select(
                'SELECT r.id, r.created_at, u.first_name, u.last_name
                   FROM cms_page_revisions r LEFT JOIN users u ON u.id = r.saved_by
                  WHERE r.page_id = :id ORDER BY r.id DESC LIMIT 15',
                ['id' => (int) $row['id']]
            );
        }

        return $this->render('admin/resources/form', [
            'pageTitle' => 'Edit ' . $def['singular'],
            'def'       => $def,
            'row'       => $row,
            'revisions' => $revisions,
        ], 'layouts/admin');
    }

    public function store(Request $request): Response
    {
        $def = $this->definition($request);
        return $this->save($def, $request, null);
    }

    public function update(Request $request): Response
    {
        $def = $this->definition($request);
        $row = $this->find($def, (int) $request->routeParam('id', 0));
        return $this->save($def, $request, $row);
    }

    public function destroy(Request $request): Response
    {
        $def = $this->definition($request);
        $row = $this->find($def, (int) $request->routeParam('id', 0));

        $this->db->delete($def['table'], 'id = :id', ['id' => (int) $row['id']]);

        foreach ($def['fields'] as $column => $field) {
            if (in_array($field['type'], ['image', 'pdf'], true) && !empty($row[$column]) && str_starts_with((string) $row[$column], 'uploads/')) {
                $this->uploads->delete((string) $row[$column]);
            }
        }

        $this->audit->log('cms.' . $def['key'] . '.deleted', $def['table'], (int) $row['id'], ['title' => $this->rowTitle($def, $row)], (int) $this->auth->id());
        $this->flashSuccess(ucfirst($def['singular']) . ' deleted.');
        return $this->redirect('/admin/cms/' . $def['key']);
    }

    /** Pages visitors asked for that do not exist — candidates for redirects. */
    public function notFound(Request $request): Response
    {
        if (!$this->auth->can('content.manage')) {
            abort(403);
        }
        if ($request->isPost()) {
            $this->db->run('DELETE FROM seo_not_found_log');
            $this->flashSuccess('404 log cleared.');
            return $this->redirect('/admin/cms/not-found');
        }
        $rows = $this->db->select('SELECT * FROM seo_not_found_log ORDER BY hits DESC, last_hit_at DESC LIMIT 300');
        return $this->render('admin/resources/not_found', [
            'pageTitle' => 'SEO: 404 log',
            'rows'      => $rows,
        ], 'layouts/admin');
    }

    /** Drag-and-drop ordering: POST ids[] in the new order. */
    public function reorder(Request $request): Response
    {
        $def = $this->definition($request);
        if (empty($def['sortable'])) {
            abort(404);
        }
        $ids = $request->input('ids', []);
        if (!is_array($ids) || $ids === [] || count($ids) > 500) {
            return Response::json(['ok' => false, 'error' => 'Nothing to reorder.'], 422);
        }
        $this->db->transaction(function () use ($def, $ids): void {
            foreach (array_values($ids) as $position => $id) {
                $this->db->update($def['table'], ['sort_order' => $position], 'id = :id', ['id' => (int) $id]);
            }
        });
        $this->audit->log('cms.' . $def['key'] . '.reordered', $def['table'], null, ['count' => count($ids)], (int) $this->auth->id());
        return Response::json(['ok' => true]);
    }

    /** Restore a page revision (cms_pages only). */
    public function restoreRevision(Request $request): Response
    {
        $def = $this->definition($request);
        if (empty($def['revisions'])) {
            abort(404);
        }
        $row = $this->find($def, (int) $request->routeParam('id', 0));
        $revision = $this->db->selectOne(
            'SELECT * FROM cms_page_revisions WHERE id = :rid AND page_id = :pid',
            ['rid' => (int) $request->routeParam('rev', 0), 'pid' => (int) $row['id']]
        );
        if ($revision === null) {
            abort(404, 'Revision not found.');
        }
        $snapshot = json_decode((string) $revision['snapshot_json'], true);
        if (!is_array($snapshot)) {
            $this->flashError('That revision is unreadable.');
            return $this->redirect('/admin/cms/pages/' . (int) $row['id'] . '/edit');
        }
        $this->snapshot($row, (int) $this->auth->id());
        $data = array_intersect_key($snapshot, $def['fields']);
        $data['updated_by'] = (int) $this->auth->id();
        $this->db->update($def['table'], $data, 'id = :id', ['id' => (int) $row['id']]);
        $this->audit->log('cms.pages.revision_restored', 'cms_pages', (int) $row['id'], ['revision_id' => (int) $revision['id']], (int) $this->auth->id());
        $this->flashSuccess('Revision restored. The previous state was saved as a new revision.');
        return $this->redirect('/admin/cms/pages/' . (int) $row['id'] . '/edit');
    }

    // ------------------------------------------------------------- internals

    /** @param array<string,mixed>|null $row */
    private function save(array $def, Request $request, ?array $row): Response
    {
        $isEdit = $row !== null;
        $input = $request->all();
        $formUrl = '/admin/cms/' . $def['key'] . ($isEdit ? '/' . (int) $row['id'] . '/edit' : '/create');

        // Derive slugs before validation so "required|slug" sees the value.
        foreach ($def['fields'] as $column => $field) {
            if ($field['type'] === 'slug') {
                $value = Str::slug(trim((string) ($input[$column] ?? '')), 150);
                if ($value === '' && !empty($field['from'])) {
                    $value = Str::slug(trim((string) ($input[$field['from']] ?? '')), 150);
                }
                $input[$column] = $value;
            }
            if ($field['type'] === 'bool') {
                $input[$column] = !empty($input[$column]) ? 1 : 0;
            }
            if (in_array($field['type'], ['int', 'decimal'], true) && isset($input[$column]) && trim((string) $input[$column]) === '') {
                unset($input[$column]);
            }
        }

        $rules = [];
        foreach ($def['fields'] as $column => $field) {
            if (!empty($field['rules']) && !in_array($field['type'], ['image', 'pdf', 'bool'], true)) {
                $rules[$column] = $field['rules'];
            }
        }
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            return $this->flashFormStateTo($validator->errors(), $input, $formUrl);
        }

        $errors = [];
        $data = [];
        foreach ($def['fields'] as $column => $field) {
            $type = $field['type'];
            if ($type === 'image' || $type === 'pdf') {
                $file = $request->file($column);
                if (is_array($file) && (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                    try {
                        $data[$column] = $type === 'image'
                            ? $this->uploads->storeImage($file, (string) ($field['folder'] ?? 'cms'))
                            : $this->uploads->storePublicDocument($file, (string) ($field['folder'] ?? 'documents'));
                        if (!empty($field['size_column'])) {
                            $data[$field['size_column']] = (int) ($file['size'] ?? 0);
                        }
                        if (!empty($field['dimension_columns']) && $type === 'image') {
                            $abs = rtrim($this->publicPath(), '/') . '/' . $data[$column];
                            $dim = @getimagesize($abs);
                            if (is_array($dim)) {
                                $data[$field['dimension_columns'][0]] = (int) $dim[0];
                                $data[$field['dimension_columns'][1]] = (int) $dim[1];
                            }
                        }
                        if ($isEdit && !empty($row[$column]) && str_starts_with((string) $row[$column], 'uploads/')) {
                            $this->uploads->delete((string) $row[$column]);
                        }
                    } catch (InvalidArgumentException | RuntimeException $e) {
                        $errors[$column] = $e->getMessage();
                    }
                } elseif (!empty($input[$column . '_remove']) && $isEdit) {
                    if (!empty($row[$column]) && str_starts_with((string) $row[$column], 'uploads/')) {
                        $this->uploads->delete((string) $row[$column]);
                    }
                    $data[$column] = null;
                } elseif (!$isEdit && !empty($field['required_on_create'])) {
                    $errors[$column] = 'Please choose a file.';
                }
                continue;
            }

            if ($type === 'bool') {
                $data[$column] = (int) $input[$column];
                continue;
            }

            $raw = $input[$column] ?? null;
            if ($raw === null || (is_string($raw) && trim($raw) === '')) {
                $data[$column] = $isEdit ? null : ($field['default'] ?? null);
                if ($type === 'select' && array_key_exists('', $field['options'] ?? [])) {
                    $data[$column] = null;
                }
                continue;
            }
            $value = is_string($raw) ? trim($raw) : $raw;

            switch ($type) {
                case 'int':
                    $data[$column] = (int) $value;
                    break;
                case 'decimal':
                    $data[$column] = (float) $value;
                    break;
                case 'datetime':
                    $data[$column] = str_replace('T', ' ', (string) $value);
                    if (strlen($data[$column]) === 16) {
                        $data[$column] .= ':00';
                    }
                    break;
                case 'json':
                    json_decode((string) $value, true, 32);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $errors[$column] = 'That is not valid JSON: ' . json_last_error_msg();
                    }
                    $data[$column] = (string) $value;
                    break;
                case 'html':
                    $data[$column] = $this->cleanHtml((string) $value);
                    break;
                case 'color':
                    $data[$column] = preg_match('/^#[0-9a-f]{3,8}$/i', (string) $value) ? (string) $value : null;
                    break;
                default:
                    $data[$column] = (string) $value;
            }
        }

        // Unique constraints we know about, reported as friendly messages.
        foreach (['slug', 'route_path', 'from_path'] as $uniqueColumn) {
            if (isset($def['fields'][$uniqueColumn]) && isset($data[$uniqueColumn])) {
                $sql = "SELECT COUNT(*) FROM `{$def['table']}` WHERE `{$uniqueColumn}` = :v";
                $params = ['v' => $data[$uniqueColumn]];
                if ($isEdit) {
                    $sql .= ' AND id <> :id';
                    $params['id'] = (int) $row['id'];
                }
                if ($this->db->int($sql, $params) > 0) {
                    $errors[$uniqueColumn] = 'That value is already in use.';
                }
            }
        }
        if (isset($def['fields']['route_path']) && isset($data['route_path']) && !str_starts_with((string) $data['route_path'], '/')) {
            $data['route_path'] = '/' . ltrim((string) $data['route_path'], '/');
        }
        if (isset($def['fields']['from_path']) && isset($data['from_path']) && empty($data['is_regex']) && !str_starts_with((string) $data['from_path'], '/')) {
            $data['from_path'] = '/' . ltrim((string) $data['from_path'], '/');
        }

        if ($errors !== []) {
            return $this->flashFormStateTo($errors, $input, $formUrl);
        }

        $userId = (int) $this->auth->id();
        if ($isEdit) {
            if (!empty($def['revisions'])) {
                $this->snapshot($row, $userId);
                $data['updated_by'] = $userId;
            }
            if (isset($def['fields']['route_path'])) {
                $data['updated_by'] = $userId;
            }
            $this->db->update($def['table'], $data, 'id = :id', ['id' => (int) $row['id']]);
            $id = (int) $row['id'];
            $this->audit->log('cms.' . $def['key'] . '.updated', $def['table'], $id, ['fields' => array_keys($data)], $userId);
            $this->flashSuccess(ucfirst($def['singular']) . ' saved.');
        } else {
            if (!empty($def['revisions'])) {
                $data['created_by'] = $userId;
                $data['updated_by'] = $userId;
            }
            if ($def['table'] === 'media_items') {
                $data['uploaded_by'] = $userId;
            }
            if (isset($def['fields']['route_path'])) {
                $data['updated_by'] = $userId;
            }
            $id = $this->db->insert($def['table'], $data);
            $this->audit->log('cms.' . $def['key'] . '.created', $def['table'], $id, ['title' => $this->rowTitle($def, $data)], $userId);
            $this->flashSuccess(ucfirst($def['singular']) . ' created.');
        }

        $stay = $request->input('_stay') === '1';
        return $this->redirect($stay ? '/admin/cms/' . $def['key'] . '/' . $id . '/edit' : '/admin/cms/' . $def['key']);
    }

    /** @param array<string,mixed> $row */
    private function snapshot(array $row, int $userId): void
    {
        $this->db->insert('cms_page_revisions', [
            'page_id'       => (int) $row['id'],
            'snapshot_json' => json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'saved_by'      => $userId,
        ]);
        // Keep the last 30 revisions per page.
        $this->db->run(
            'DELETE FROM cms_page_revisions WHERE page_id = :p AND id NOT IN (
                SELECT id FROM (SELECT id FROM cms_page_revisions WHERE page_id = :p2 ORDER BY id DESC LIMIT 30) keep
             )',
            ['p' => (int) $row['id'], 'p2' => (int) $row['id']]
        );
    }

    /**
     * Staff-authored HTML is trusted for layout but scripts and event handlers
     * are stripped so a compromised content account cannot inject JavaScript.
     */
    private function cleanHtml(string $html): string
    {
        $html = preg_replace('#<(script|iframe|object|embed|style|form|input|button)\b[^>]*>.*?</\1>#is', '', $html) ?? $html;
        $html = preg_replace('#<(script|iframe|object|embed|style|form|input|button)\b[^>]*/?>#i', '', $html) ?? $html;
        $html = preg_replace('/\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? $html;
        $html = preg_replace('/(href|src)\s*=\s*(["\']?)\s*javascript:[^"\'>\s]*/i', '$1=$2#', $html) ?? $html;
        return trim($html);
    }

    /** @return array<string,mixed> */
    private function definition(Request $request): array
    {
        $key = (string) $request->routeParam('resource', '');
        $def = ResourceRegistry::get($key);
        if ($def === null) {
            abort(404, 'Unknown section.');
        }
        if (!$this->auth->can((string) $def['permission'])) {
            abort(403, 'Your role does not include permission for ' . $def['label'] . '.');
        }
        return $def;
    }

    /** @return array<string,mixed> */
    private function find(array $def, int $id): array
    {
        $row = $id > 0 ? $this->db->selectOne("SELECT * FROM `{$def['table']}` WHERE id = :id", ['id' => $id]) : null;
        if ($row === null) {
            abort(404, ucfirst($def['singular']) . ' not found.');
        }
        return $row;
    }

    private function rowTitle(array $def, array $row): string
    {
        foreach (['title', 'name', 'bank_name', 'route_path', 'from_path', 'slug'] as $column) {
            if (!empty($row[$column])) {
                return (string) $row[$column];
            }
        }
        return '#' . (string) ($row['id'] ?? '');
    }

    private function publicPath(): string
    {
        return dirname(__DIR__, 4);
    }
}
