<?php
/**
 * Permanently deleted records, with who deleted them, when and why.
 * @var array $rows @var int $total @var int $page @var int $lastPage @var array $filters @var array<string,string> $types
 */
$labels = \App\Http\Controllers\Admin\DeletedRecordsController::TYPES;
?>
<div class="dash-head">
  <div>
    <h1>Deleted records</h1>
    <p class="sub">Everything permanently deleted, with the reason given. These entries no longer appear anywhere else and cannot be restored from here.</p>
  </div>
</div>

<section class="panel">
  <form class="toolbar" method="get" action="<?= e(base_url('admin/deleted-records')) ?>">
    <div class="search-field">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
      <input class="input" type="search" name="q" value="<?= e((string) $filters['q']) ?>" placeholder="Reference, reason or staff email">
    </div>
    <div class="select-field">
      <select class="select-field" name="type" data-autosubmit aria-label="Kind of record">
        <option value="">All kinds</option>
        <?php foreach ($types as $key => $label): ?>
          <option value="<?= e($key) ?>" <?= $filters['type'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button class="btn btn-brand" type="submit">Filter</button>
    <?php if ($filters['q'] !== '' || $filters['type'] !== ''): ?><a class="btn btn-light" href="<?= e(base_url('admin/deleted-records')) ?>">Clear</a><?php endif; ?>
  </form>

  <p class="text-muted"><?= e(number_format($total)) ?> deleted record<?= $total === 1 ? '' : 's' ?>.</p>

  <?php if ($rows === []): ?>
    <div class="empty-state"><h3>Nothing deleted</h3><p>Deleted donations and entries are listed here with the reason.</p></div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Deleted</th><th>Kind</th><th>Reference</th><th class="wrap">What it was</th><th class="wrap">Reason</th><th>Deleted by</th></tr></thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= e(dt((string) $r['deleted_at'], 'j M Y H:i')) ?> UTC</td>
              <td><?= e($labels[$r['record_type']][0] ?? (string) $r['record_type']) ?></td>
              <td class="mono"><?= e((string) ($r['reference'] ?? '—')) ?></td>
              <td class="wrap"><?= e((string) $r['summary']) ?></td>
              <td class="wrap"><?= nl2br(e((string) $r['reason'])) ?></td>
              <td class="wrap"><?= e((string) ($r['deleted_by_email'] ?? 'unknown')) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php $query = ['q' => $filters['q'], 'type' => $filters['type']]; require __DIR__ . '/../partials/pagination.php'; ?>
  <?php endif; ?>
</section>
