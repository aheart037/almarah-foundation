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
    if ($key === 'interests_json' || $key === 'details_json') { $arr = json_decode((string) $value, true); if (is_array($arr)) { $parts=[]; foreach ($arr as $k=>$v) { if ($v !== null && $v !== '' && $v !== []) $parts[] = $k . ': ' . (is_array($v) ? implode(', ', $v) : (string)$v); } return e(implode(' | ', $parts)); } return e((string)$value); }
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
      <?php if ($box['key'] === 'contact'): ?>
        <div class="field"><label for="assigned_to">Assign to staff member</label>
          <select class="select-field" id="assigned_to" name="assigned_to"><option value="">Unassigned</option>
            <?php foreach ($staffList as $staff): ?><option value="<?= (int)$staff['id'] ?>" <?= (int)($row['assigned_to'] ?? 0)===(int)$staff['id'] ? 'selected' : '' ?>><?= e(trim($staff['first_name'].' '.$staff['last_name']).' ('.$staff['email'].')') ?></option><?php endforeach; ?>
          </select></div>
        <div class="field"><label for="response_text">Record a response (optional)</label>
          <textarea class="textarea" id="response_text" name="response_text" rows="5" maxlength="5000" placeholder="Your reply to the sender…"></textarea></div>
        <label class="choice"><input type="checkbox" name="send_response" value="1"> Email this response to the sender</label>
      <?php elseif ($box['key'] === 'volunteers'): ?>
        <div class="field"><label for="campaign_id">Assign to existing campaign</label>
          <select class="select-field" id="campaign_id" name="campaign_id"><option value="">Unassigned</option>
            <?php foreach ($campaignList as $campaign): ?><option value="<?= (int)$campaign['id'] ?>" <?= (int)($row['campaign_id'] ?? 0)===(int)$campaign['id'] ? 'selected' : '' ?>><?= e((string)$campaign['title']) ?></option><?php endforeach; ?>
          </select></div>
        <div class="field"><label for="team_id">Assign to existing fundraising team</label>
          <select class="select-field" id="team_id" name="team_id"><option value="">Unassigned</option>
            <?php foreach ($teamList as $team): ?><option value="<?= (int)$team['id'] ?>" <?= (int)($row['team_id'] ?? 0)===(int)$team['id'] ? 'selected' : '' ?>><?= e((string)$team['name']) ?></option><?php endforeach; ?>
          </select><small>This records a volunteer assignment; it does not grant a fundraising-team user account.</small></div>
      <?php endif; ?>
      <?php if (!empty($box['reviewed']) && !empty($row['reviewed_at'])): ?>
        <p class="form-help">Last reviewed <?= e(dt((string) $row['reviewed_at'], 'j M Y, H:i')) ?><?= $reviewer ? ' by ' . e(trim($reviewer['first_name'] . ' ' . $reviewer['last_name'])) : '' ?>.</p>
      <?php endif; ?>
      <button class="btn btn-brand" type="submit">Save</button>
    </form>
    <hr style="margin:22px 0;border:0;border-top:1px solid #eee3e8">
    <?php if (!in_array($box['key'], ['contact','volunteers'], true)): ?>
    <form method="post" action="<?= e($base . '/' . $id . '/delete') ?>" data-confirm="Permanently delete this record and any attached file? This cannot be undone.">
      <?= csrf_field() ?>
      <button class="btn btn-danger btn-sm" type="submit">Delete permanently</button>
      <span class="form-help" style="display:inline-block;margin-left:10px">Use for spam or data-removal requests.</span>
    </form>
    <?php endif; ?>
  </section>
</div>
<?php if (in_array($box['key'], ['contact','volunteers'], true)): ?>
<section class="panel" style="margin-top:20px"><h3>Submission and activity history</h3>
  <?php foreach ($history as $event): ?><p><strong><?= e((string)$event['event_type']) ?></strong> · <?= e((string)$event['created_at']) ?> · <?= e((string)($event['actor_email'] ?? 'Website / migration')) ?>
    <?php if ($event['from_status'] !== null || $event['to_status'] !== null): ?> (<?= e((string)($event['from_status'] ?? '—')) ?> → <?= e((string)($event['to_status'] ?? '—')) ?>)<?php endif; ?>
    <?php if ($event['note']): ?><br><?= nl2br(e((string)$event['note'])) ?><?php endif; ?>
  </p><?php endforeach; ?>
</section>
<?php endif; ?>
