<?php
/**
 * Generic CMS list page driven by ResourceRegistry.
 *
 * @var array  $def
 * @var array  $rows
 * @var string $q
 * @var array  $filters
 * @var int    $page
 * @var int    $lastPage
 * @var int    $total
 * @var array  $query
 */
$base = base_url('admin/cms/' . $def['key']);
$sortable = !empty($def['sortable']) && $q === '' && $filters === [];
$cell = static function (array $row, string $column, array $def): string {
    $field = $def['fields'][$column] ?? ['type' => 'text'];
    $value = $row[$column] ?? null;
    switch ($field['type'] ?? 'text') {
        case 'image':
            if ($value === null || $value === '') {
                return '<span class="muted">—</span>';
            }
            return '<img class="rs-thumb" src="' . e(base_url((string) $value)) . '" alt="" loading="lazy">';
        case 'pdf':
            return $value ? '<a href="' . e(base_url((string) $value)) . '" target="_blank" rel="noopener">PDF</a>' : '<span class="muted">—</span>';
        case 'bool':
            return $value ? '<span class="badge badge-active">yes</span>' : '<span class="badge badge-grey">no</span>';
        case 'select':
            $label = (string) ($field['options'][(string) $value] ?? $value ?? '—');
            if (in_array($column, ['status', 'is_active'], true) || $column === 'status') {
                $cls = in_array((string) $value, ['published', 'active', 'open'], true) ? 'badge-active' : 'badge-grey';
                return '<span class="badge ' . $cls . '">' . e($label) . '</span>';
            }
            return e($label);
        case 'date':
            return $value ? e(dt((string) $value, 'j M Y')) : '<span class="muted">—</span>';
        case 'datetime':
            return $value ? e(dt((string) $value, 'j M Y H:i')) : '<span class="muted">—</span>';
        default:
            if ($column === 'updated_at' || $column === 'created_at') {
                return $value ? e(dt((string) $value, 'j M Y H:i')) : '—';
            }
            $text = (string) ($value ?? '');
            if (mb_strlen($text) > 70) {
                $text = mb_substr($text, 0, 68) . '…';
            }
            return $text === '' ? '<span class="muted">—</span>' : e($text);
    }
};
$firstColumn = array_key_first($def['list']);
?>
<div class="dash-head">
  <div>
    <h1><?= e($def['label']) ?></h1>
    <?php if (!empty($def['help'])): ?><p class="sub"><?= e($def['help']) ?></p><?php endif; ?>
  </div>
  <div class="sr-actions">
    <?php foreach ($def['actions'] ?? [] as $action): ?>
      <a class="btn btn-light" href="<?= e(base_url((string) $action['href'])) ?>"><?= e((string) $action['label']) ?></a>
    <?php endforeach; ?>
    <a class="btn btn-brand" href="<?= e($base . '/create') ?>">New <?= e($def['singular']) ?></a>
  </div>
</div>

<section class="panel">
  <form method="get" action="<?= e($base) ?>" class="rs-toolbar">
    <input class="input" type="search" name="q" value="<?= e($q) ?>" placeholder="Search…" aria-label="Search">
    <?php foreach ($def['filters'] ?? [] as $column => $options): ?>
      <select class="select-field" name="<?= e($column) ?>" aria-label="<?= e(ucfirst($column)) ?>">
        <option value=""><?= e((string) ($def['filter_all'][$column] ?? 'All ' . $column . 's')) ?></option>
        <?php foreach ($options as $value => $label): ?>
          <option value="<?= e((string) $value) ?>" <?= ($filters[$column] ?? '') === (string) $value ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
      </select>
    <?php endforeach; ?>
    <button class="btn btn-light" type="submit">Filter</button>
    <?php if ($q !== '' || $filters !== []): ?><a class="btn btn-light" href="<?= e($base) ?>">Clear</a><?php endif; ?>
    <span class="muted rs-count"><?= e((string) $total) ?> <?= $total === 1 ? 'item' : 'items' ?></span>
  </form>

  <?php if ($rows === []): ?>
    <div class="empty-state">
      <h3>Nothing here yet</h3>
      <p><?= $q !== '' ? 'No results match your search.' : 'Add the first ' . e($def['singular']) . ' and it will show up on the website straight away.' ?></p>
      <a class="btn btn-brand" href="<?= e($base . '/create') ?>">New <?= e($def['singular']) ?></a>
    </div>
  <?php else: ?>
    <?php if ($sortable): ?>
      <p class="form-help rs-drag-hint">Drag the handle to reorder. The order is saved automatically.</p>
    <?php endif; ?>
    <div class="table-wrap">
      <table class="data-table rs-table" <?= $sortable ? 'data-reorder-url="' . e($base . '/reorder') . '"' : '' ?>>
        <thead>
          <tr>
            <?php if ($sortable): ?><th style="width:34px"></th><?php endif; ?>
            <?php foreach ($def['list'] as $column => $label): ?><th><?= e($label) ?></th><?php endforeach; ?>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $row): $id = (int) $row['id']; ?>
            <tr data-id="<?= $id ?>">
              <?php if ($sortable): ?>
                <td class="rs-handle" title="Drag to reorder" aria-label="Drag to reorder" draggable="true">
                  <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><circle cx="9" cy="6" r="1.6"/><circle cx="15" cy="6" r="1.6"/><circle cx="9" cy="12" r="1.6"/><circle cx="15" cy="12" r="1.6"/><circle cx="9" cy="18" r="1.6"/><circle cx="15" cy="18" r="1.6"/></svg>
                </td>
              <?php endif; ?>
              <?php foreach ($def['list'] as $column => $label): ?>
                <td class="<?= $column === $firstColumn ? 'wrap' : '' ?>">
                  <?php if ($column === $firstColumn && ($def['fields'][$column]['type'] ?? '') !== 'image'): ?>
                    <b><a href="<?= e($base . '/' . $id . '/edit') ?>"><?= $cell($row, $column, $def) ?></a></b>
                  <?php else: ?>
                    <?= $cell($row, $column, $def) ?>
                  <?php endif; ?>
                </td>
              <?php endforeach; ?>
              <td class="sr-actions" style="white-space:nowrap;text-align:right">
                <?php if (!empty($def['preview']) && !empty($row['slug'])): ?>
                  <a class="btn btn-light btn-sm" href="<?= e(base_url(ltrim(str_replace('{slug}', (string) $row['slug'], $def['preview']), '/'))) ?>" target="_blank" rel="noopener">View</a>
                <?php endif; ?>
                <a class="btn btn-light btn-sm" href="<?= e($base . '/' . $id . '/edit') ?>">Edit</a>
                <form method="post" action="<?= e($base . '/' . $id . '/delete') ?>" class="inline-form" data-confirm="Delete this <?= e($def['singular']) ?>? This cannot be undone.">
                  <?= csrf_field() ?>
                  <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php require __DIR__ . '/../../partials/pagination.php'; ?>
  <?php endif; ?>
</section>
