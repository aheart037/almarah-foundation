<?php
/**
 * Backups: readiness checks, run buttons, history with download/delete.
 * @var array $status @var array $logs
 */
$ok = static fn (bool $b): string => $b ? '<span class="badge badge-green">OK</span>' : '<span class="badge badge-amber">Check</span>';
$last = $status['last_completed'];
?>
<div class="dash-head">
  <div>
    <h1>Backups</h1>
    <p class="sub">Database and uploaded-file backups, stored outside the web root<?= $status['encrypted'] ? ' and encrypted' : '' ?>.</p>
  </div>
</div>

<div class="stat-cards">
  <div class="stat-card <?= $last && $status['last_age_hours'] <= 48 ? 'brand' : '' ?>">
    <div class="k">Last successful backup</div>
    <div class="v"><?= $last ? e($status['last_age_hours'] < 1 ? 'just now' : $status['last_age_hours'] . ' h ago') : 'never' ?></div>
    <div class="d"><?= $last ? e(ucfirst((string) $last['kind']) . ' · ' . human_size((int) $last['size_bytes']) . ' · ' . dt((string) $last['finished_at'], 'j M Y H:i')) : 'Run one below, then schedule the cron job.' ?></div>
  </div>
  <div class="stat-card">
    <div class="k">Storage used</div>
    <div class="v"><?= e(human_size((int) $status['disk_used_bytes'])) ?></div>
    <div class="d">kept for <?= (int) $status['keep_days'] ?> days (always the 3 newest of each kind)<?= $status['disk_free_bytes'] ? ' · ' . e(human_size((int) $status['disk_free_bytes'])) . ' free' : '' ?></div>
  </div>
  <div class="stat-card">
    <div class="k">Off-site copy</div>
    <div class="v"><?= $status['offsite'] ? 'On' : 'Off' ?></div>
    <div class="d"><?= $status['offsite'] ? 'pushed after every backup' : 'set backup_offsite_cmd in config.php (e.g. rclone) or download manually' ?></div>
  </div>
</div>

<div class="grid-2">
  <section class="panel">
    <h2>Create a backup now</h2>
    <p class="muted">Large uploads folders can take a minute — please wait for the page to return.</p>
    <form method="post" action="<?= e(base_url('admin/backups/run')) ?>" class="sr-actions">
      <?= csrf_field() ?>
      <button class="btn btn-brand" name="kind" value="database" type="submit">Database</button>
      <button class="btn btn-outline-brand" name="kind" value="uploads" type="submit">Uploaded files</button>
      <button class="btn btn-outline-brand" name="kind" value="full" type="submit">Full (both)</button>
    </form>
    <hr>
    <h3 style="margin:0 0 8px">Readiness</h3>
    <dl class="kv">
      <dt>Folder</dt><dd class="mono" style="font-weight:500"><?= e((string) $status['path']) ?></dd>
      <dt>Writable</dt><dd><?= $ok((bool) $status['writable']) ?></dd>
      <dt>Outside web root</dt><dd><?= $ok(!$status['inside_web_root']) ?><?= $status['inside_web_root'] ? ' <span class="muted">— the folder is protected by .htaccess, but set backup_path above public_html</span>' : '' ?></dd>
      <dt>Encryption</dt><dd><?= $ok((bool) $status['encrypted']) ?> <span class="muted"><?= $status['encrypted'] ? 'AES-256; keep the passphrase from config.php somewhere safe' : 'set backup_passphrase in config.php' ?></span></dd>
      <dt>mysqldump</dt><dd><?= $status['mysqldump'] ? '<span class="badge badge-green">Available</span>' : '<span class="badge badge-grey">Not available</span> <span class="muted">— built-in PHP dump is used instead</span>' ?></dd>
      <dt>Zip extension</dt><dd><?= $ok((bool) $status['zip']) ?></dd>
    </dl>
  </section>

  <section class="panel" style="min-width:0">
    <h2>Schedule (cron)</h2>
    <p class="muted">In hPanel → Advanced → Cron Jobs add these (adjust the path to your site folder):</p>
    <pre class="mono" style="overflow:auto;font-size:12.5px;background:#faf7f5;padding:12px;border-radius:8px;margin:0 0 12px">SITE=/home/USER/domains/almarah.org/public_html

# nightly database backup, 02:10
10 2 * * *   cd $SITE && php bin/console backups:run database --quiet
# weekly full backup, Sundays 03:10
10 3 * * 0   cd $SITE && php bin/console backups:run full --quiet
# outgoing email every 5 min, pending-payment checks every 10 min
*/5 * * * *  cd $SITE && php bin/console mail:work --quiet
*/10 * * * * cd $SITE && php bin/console payments:reconcile</pre>
    <p class="muted">Restore: download the file, decrypt with <code>php bin/console backups:decrypt FILE</code>, then import the .sql.gz through phpMyAdmin or <code>mysql</code>. Uploaded files unzip straight back into the site folder.</p>
  </section>
</div>

<section class="panel">
  <h2>History</h2>
  <?php if ($logs === []): ?><p class="muted">No backups yet.</p><?php else: ?>
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>When</th><th>Kind</th><th>File</th><th class="num">Size</th><th>Status</th><th>Off-site</th><th>By</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($logs as $l): ?>
        <tr>
          <td style="white-space:nowrap"><?= e(dt((string) $l['started_at'], 'j M Y H:i')) ?></td>
          <td><?= e(ucfirst((string) $l['kind'])) ?></td>
          <td class="mono wrap" style="font-size:12px"><?= e((string) $l['file_name']) ?><?= !empty($l['checksum_sha256']) ? '<div class="muted" title="SHA-256">' . e(substr((string) $l['checksum_sha256'], 0, 16)) . '…</div>' : '' ?></td>
          <td class="num"><?= $l['size_bytes'] ? e(human_size((int) $l['size_bytes'])) : '—' ?></td>
          <td><span class="<?= e(status_badge_class((string) $l['status'])) ?>"><?= e((string) $l['status']) ?></span><?php if ($l['status'] === 'failed'): ?><div class="muted" style="font-size:12px;max-width:240px"><?= e((string) $l['message']) ?></div><?php endif; ?></td>
          <td><?= e(str_replace('_', ' ', (string) $l['offsite_status'])) ?></td>
          <td><?= e((string) ($l['triggered_by_name'] ?: 'cron')) ?></td>
          <td style="white-space:nowrap">
            <?php if ($l['status'] === 'completed'): ?>
              <a class="btn btn-light btn-sm" href="<?= e(base_url('admin/backups/' . (int) $l['id'] . '/download')) ?>">Download</a>
              <form method="post" action="<?= e(base_url('admin/backups/' . (int) $l['id'] . '/delete')) ?>" style="display:inline" data-confirm="Delete this backup file?"><?= csrf_field() ?><button class="btn btn-light btn-sm" type="submit">Delete</button></form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</section>
