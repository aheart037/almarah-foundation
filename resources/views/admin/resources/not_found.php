<?php /** @var array $rows */ ?>
<div class="dash-head">
  <div>
    <h1>404 log</h1>
    <p class="sub">Addresses visitors (and Google) requested that no longer exist. Add a redirect for anything that used to be a real page.</p>
  </div>
  <div class="sr-actions">
    <a class="btn btn-light" href="<?= e(base_url('admin/cms/redirects')) ?>">Redirects</a>
    <form method="post" action="<?= e(base_url('admin/cms/not-found')) ?>" class="inline-form" data-confirm="Clear the whole 404 log?"><?= csrf_field() ?><button class="btn btn-light" type="submit">Clear log</button></form>
  </div>
</div>
<section class="panel">
  <?php if ($rows === []): ?>
    <div class="empty-state"><h3>No missing pages recorded</h3><p>Good news — nobody has hit a dead link recently.</p></div>
  <?php else: ?>
    <div class="table-wrap"><table class="data-table">
      <thead><tr><th>Path</th><th class="num">Hits</th><th>Last hit</th><th>Referrer</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td class="wrap mono"><?= e((string) $r['path']) ?></td>
          <td class="num"><?= (int) $r['hits'] ?></td>
          <td><?= e(dt((string) $r['last_hit_at'], 'j M Y H:i')) ?></td>
          <td class="muted" style="max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e((string) ($r['referer'] ?? '')) ?></td>
          <td style="text-align:right"><a class="btn btn-light btn-sm" href="<?= e(base_url('admin/cms/redirects/create?from_path=' . rawurlencode((string) $r['path']))) ?>">Add redirect</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody></table></div>
  <?php endif; ?>
</section>
