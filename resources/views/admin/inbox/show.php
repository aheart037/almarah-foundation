<?php
/**
 * Single submission.
 * @var array $box @var array $row @var array|null $reviewer
 */
$base = base_url('admin/inbox/' . $box['key']);
$id = (int) $row['id'];
$title = (string) ($row[$box['title']] ?? '');
if ($box['title'] === 'first_name') { $title = trim($title . ' ' . (string) ($row['last_name'] ?? '')); }
$fmt = static function (string $key, $value) use ($box): string {
    if ($value === null || $value === '') { return '<span class="muted">—</span>'; }
    if (in_array($key, ['created_at', 'unsubscribed_at', 'reviewed_at'], true)) { return e(dt((string) $value, 'j M Y, H:i')); }
    if ($key === 'email') { return '<a href="mailto:' . e((string) $value) . '">' . e((string) $value) . '</a>'; }
    if ($key === 'phone' || $key === 'alt_phone') { return '<a href="tel:' . e(preg_replace('/[^0-9+]/', '', (string) $value)) . '">' . e((string) $value) . '</a>'; }
    if ($key === 'linkedin_url') { return '<a href="' . e((string) $value) . '" target="_blank" rel="noopener noreferrer">' . e((string) $value) . '</a>'; }
    if ($key === 'interests_json') { $arr = json_decode((string) $value, true); return is_array($arr) ? e(implode(', ', array_map('strval', $arr))) : e((string) $value); }
    if ($key === 'goal_amount') { return 'Rs ' . e(number_format((float) $value)); }
    if (in_array($key, $box['long'], true)) { return '<div class="msg-body">' . e((string) $value) . '</div>'; }
    return e((string) $value);
};
?>
<div class="dash-head">
  <div>
    <h1><?= e($title !== '' ? $title : $box['label']) ?></h1>
    <p class="sub"><?= e($box['label']) ?> · #<?= $id ?> · received <?= e(dt((string) $row['created_at'], 'j M Y, H:i')) ?></p>
  </div>
  <div class="sr-actions">
    <?php if (!empty($row['email'])): ?><a class="btn btn-brand" href="mailto:<?= e((string) $row['email']) ?>?subject=<?= rawurlencode('Re: ' . ($row['subject'] ?? $row['position'] ?? $row['campaign_title'] ?? 'Your message to Almarah Foundation')) ?>">Reply by email</a><?php endif; ?>
    <a class="btn btn-light" href="<?= e($base) ?>">Back</a>
  </div>
</div>

<div class="grid-2">
  <section class="panel">
    <div class="panel-head"><h3>Details</h3></div>
    <div class="detail-grid">
      <?php foreach ($box['detail'] as $key => $label): ?>
        <?php if ($key === 'resume_original_name'): ?>
          <div class="full"><div class="k"><?= e($label) ?></div><div class="v">
            <?php if (!empty($row['resume_path'])): ?>
              <a class="btn btn-light btn-sm" href="<?= e($base . '/' . $id . '/resume') ?>">Download <?= e((string) ($row['resume_original_name'] ?: 'CV')) ?></a>
            <?php else: ?><span class="muted">No CV attached</span><?php endif; ?>
          </div></div>
        <?php else: ?>
          <div class="<?= in_array($key, $box['long'], true) ? 'full' : '' ?>"><div class="k"><?= e($label) ?></div><div class="v"><?= $fmt($key, $row[$key] ?? null) ?></div></div>
        <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="panel">
    <div class="panel-head"><h3>Status &amp; notes</h3><p class="panel-sub">Internal only — the sender never sees this.</p></div>
    <form method="post" action="<?= e($base . '/' . $id) ?>">
      <?= csrf_field() ?>
      <div class="field">
        <label for="status">Status</label>
        <select class="select-field" id="status" name="status">
          <?php foreach ($box['statuses'] as $value => $label): ?>
            <option value="<?= e($value) ?>" <?= (string) $row['status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php if (empty($box['no_notes'])): ?>
        <div class="field">
          <label for="admin_notes">Notes</label>
          <textarea class="textarea" id="admin_notes" name="admin_notes" rows="6" maxlength="10000" placeholder="Call back on Monday, spoke to…"><?= e((string) ($row['admin_notes'] ?? '')) ?></textarea>
        </div>
      <?php endif; ?>
      <?php if (!empty($box['reviewed']) && !empty($row['reviewed_at'])): ?>
        <p class="form-help">Last reviewed <?= e(dt((string) $row['reviewed_at'], 'j M Y, H:i')) ?><?= $reviewer ? ' by ' . e(trim($reviewer['first_name'] . ' ' . $reviewer['last_name'])) : '' ?>.</p>
      <?php endif; ?>
      <button class="btn btn-brand" type="submit">Save</button>
    </form>
    <hr style="margin:22px 0;border:0;border-top:1px solid #eee3e8">
    <form method="post" action="<?= e($base . '/' . $id . '/delete') ?>" data-confirm="Permanently delete this record and any attached file? This cannot be undone.">
      <?= csrf_field() ?>
      <button class="btn btn-danger btn-sm" type="submit">Delete permanently</button>
      <span class="form-help" style="display:inline-block;margin-left:10px">Use for spam or data-removal requests.</span>
    </form>
  </section>
</div>
