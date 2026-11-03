<?php
/**
 * Form submissions list.
 * @var array $box @var array $boxes @var array $openCounts @var array $rows @var string $q @var string $status
 * @var array $statusCounts @var int $page @var int $lastPage @var int $total @var array $query
 */
$base = base_url('admin/inbox/' . $box['key']);
$badge = static function (string $s): string {
    $map = ['new' => 'badge-pending', 'pending' => 'badge-pending', 'read' => 'badge-grey', 'replied' => 'badge-approved', 'approved' => 'badge-approved', 'active' => 'badge-active', 'subscribed' => 'badge-active', 'hired' => 'badge-completed', 'shortlisted' => 'badge-processing', 'interview' => 'badge-processing', 'contacted' => 'badge-processing', 'rejected' => 'badge-rejected', 'spam' => 'badge-failed', 'bounced' => 'badge-failed', 'unsubscribed' => 'badge-inactive', 'inactive' => 'badge-inactive', 'archived' => 'badge-archived'];
    return 'badge ' . ($map[$s] ?? 'badge-grey');
};
?>
<div class="dash-head">
  <div>
    <h1>Inbox</h1>
    <p class="sub">Everything submitted through the website forms. Nothing here is public.</p>
  </div>
  <a class="btn btn-light" href="<?= e($base . '/export' . ($status !== '' ? '?status=' . urlencode($status) : '')) ?>">Export CSV</a>
</div>

<div class="inbox-tabs">
  <?php foreach ($boxes as $k => $b): ?>
    <a href="<?= e(base_url('admin/inbox/' . $k)) ?>" class="<?= $k === $box['key'] ? 'active' : '' ?>"><?= e($b['label']) ?><?php if (!empty($openCounts[$k])): ?><span class="n">(<?= (int) $openCounts[$k] ?>)</span><?php endif; ?></a>
  <?php endforeach; ?>
</div>

<section class="panel">
  <form method="get" action="<?= e($base) ?>" class="rs-toolbar">
    <input class="input" type="search" name="q" value="<?= e($q) ?>" placeholder="Search name, email…" aria-label="Search">
    <select class="select-field" name="status" aria-label="Status">
      <option value="">All statuses</option>
      <?php foreach ($box['statuses'] as $value => $label): ?>
        <option value="<?= e($value) ?>" <?= $status === $value ? 'selected' : '' ?>><?= e($label) ?> (<?= (int) ($statusCounts[$value] ?? 0) ?>)</option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn-light" type="submit">Filter</button>
    <?php if ($q !== '' || $status !== ''): ?><a class="btn btn-light" href="<?= e($base) ?>">Clear</a><?php endif; ?>
    <span class="muted rs-count"><?= (int) $total ?> <?= $total === 1 ? 'record' : 'records' ?></span>
  </form>

  <?php if ($rows === []): ?>
    <div class="empty-state"><h3>Nothing to show</h3><p><?= $q !== '' || $status !== '' ? 'No records match the filter.' : 'New submissions will appear here the moment someone sends the form.' ?></p></div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><?php foreach ($box['list'] as $c => $l): ?><th><?= e($l) ?></th><?php endforeach; ?><th></th></tr></thead>
        <tbody>
          <?php foreach ($rows as $row): $id = (int) $row['id']; $first = true; ?>
            <tr>
              <?php foreach ($box['list'] as $c => $l): ?>
                <td class="<?= $first ? 'wrap' : '' ?>">
                  <?php
                    $v = (string) ($row[$c] ?? '');
                    if ($c === 'first_name') { $v = trim($v . ' ' . (string) ($row['last_name'] ?? '')); }
                    if ($c === 'created_at') { $v = dt($v, 'j M Y H:i'); }
                    if ($c === 'goal_amount' && $v !== '') { $v = 'Rs ' . number_format((float) $v); }
                    if (mb_strlen($v) > 60) { $v = mb_substr($v, 0, 58) . '…'; }
                  ?>
                  <?php if ($c === 'status'): ?>
                    <span class="<?= e($badge($v)) ?>"><?= e($box['statuses'][$v] ?? $v) ?></span>
                  <?php elseif ($first): ?>
                    <b><a href="<?= e($base . '/' . $id) ?>"><?= e($v !== '' ? $v : '—') ?></a></b>
                  <?php else: ?>
                    <?= $v !== '' ? e($v) : '<span class="muted">—</span>' ?>
                  <?php endif; ?>
                </td>
                <?php $first = false; ?>
              <?php endforeach; ?>
              <td style="text-align:right"><a class="btn btn-light btn-sm" href="<?= e($base . '/' . $id) ?>">Open</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php require __DIR__ . '/../../partials/pagination.php'; ?>
  <?php endif; ?>
</section>
