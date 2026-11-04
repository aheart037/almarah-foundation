<?php
/**
 * Single user administration.
 *
 * @var array $user
 * @var array $roles
 * @var array $availableRoles
 * @var array $fundraisers
 * @var array $donations
 * @var array $auditTrail
 * @var bool $isSelf
 */

$id = (int) $user['id'];
$currentRoles = array_map(
    static fn ($role): string => is_array($role) ? (string) ($role['name'] ?? '') : (string) $role,
    $roles
);
$currentRoles = array_values(array_filter($currentRoles, static fn (string $r): bool => $r !== ''));
$name = trim((string) $user['first_name'] . ' ' . (string) $user['last_name']);
$canManageRoles = app(\App\Services\AuthService::class)->isSuperAdmin();
?>
<div class="dash-head">
  <div>
    <h1><?= e($name !== '' ? $name : (string) $user['email']) ?></h1>
    <p class="sub">
      <span class="<?= e(status_badge_class((string) $user['status'])) ?>"><?= e((string) $user['status']) ?></span>
      <?php if (!empty($user['email_verified_at'])): ?>
        <span class="badge badge-green">email verified</span>
      <?php else: ?>
        <span class="badge badge-amber">email unverified</span>
      <?php endif; ?>
      <?php if ($isSelf): ?><span class="badge badge-featured">that's you</span><?php endif; ?>
    </p>
  </div>
  <a class="btn btn-light" href="<?= e(base_url('admin/users')) ?>">Back to users</a>
</div>

<div class="grid-2">
  <section class="panel">
    <h2>Account</h2>
    <dl class="kv">
      <dt>Email</dt><dd><?= e((string) $user['email']) ?></dd>
      <dt>Phone</dt><dd><?= e((string) ($user['phone'] ?? '—')) ?></dd>
      <dt>Status</dt><dd><?= e((string) $user['status']) ?></dd>
      <dt>Roles</dt>
      <dd>
        <?php foreach ($currentRoles as $role): ?>
          <span class="badge badge-<?= e($role === 'super_admin' ? 'super_admin' : $role) ?>"><?= e(str_replace('_', ' ', $role)) ?></span>
        <?php endforeach; ?>
      </dd>
      <dt>Joined</dt><dd><?= e(dt((string) $user['created_at'], 'j F Y')) ?></dd>
      <dt>Last sign-in</dt><dd><?= e($user['last_login_at'] !== null ? dt((string) $user['last_login_at'], 'j M Y H:i') . ' UTC' : 'Never') ?></dd>
      <dt>Last IP</dt><dd class="mono"><?= e((string) ($user['last_login_ip'] ?? '—')) ?></dd>
      <dt>Marketing opt-in</dt><dd><?= !empty($user['marketing_opt_in']) ? 'Yes' : 'No' ?></dd>
    </dl>
  </section>

  <?php if (app(\App\Services\AuthService::class)->canAny(['users.edit','users.manage']) && (!app(\App\Services\RbacService::class)->isSuper($id) || $canManageRoles)): ?>
  <section class="panel"><h2>Edit account</h2><form method="post" action="<?= e(base_url('admin/users/'.$id.'/edit')) ?>"><?= csrf_field() ?>
  <?php foreach (['first_name'=>'First name','last_name'=>'Last name','email'=>'Email','phone'=>'Phone'] as $field=>$label): ?>
  <div class="field"><label for="<?= e($field) ?>"><?= e($label) ?></label><input class="input" id="<?= e($field) ?>" name="<?= e($field) ?>" value="<?= e((string)($user[$field] ?? '')) ?>" maxlength="<?= $field==='email' ? '191' : ($field==='phone' ? '40' : '100') ?>" <?= $field==='phone' ? '' : 'required' ?> <?= $field==='email' ? 'type="email"' : '' ?>></div>
  <?php endforeach; ?><button class="btn btn-brand" type="submit">Save details</button></form></section><?php endif; ?>

  <?php if (app(\App\Services\AuthService::class)->canAny(['users.edit','users.manage'])): ?><section class="panel">
    <h2>Actions</h2>
    <p class="panel-sub">Passwords are never displayed or set from here — we email a reset link instead.</p>

    <?php if (!app(\App\Services\RbacService::class)->isSuper($id) || $canManageRoles): ?>
    <div class="form-actions" style="flex-direction:column;align-items:stretch;gap:14px">
      <?php if (!empty($user['email_verified_at'])): ?>
        <p class="text-muted mb-0">Email address is already verified.</p>
      <?php else: ?>
        <form method="post" action="<?= e(base_url('admin/users/' . $id . '/verify-email')) ?>" data-confirm="Mark this email address as verified?">
          <?= csrf_field() ?>
          <button class="btn btn-light" type="submit">Mark email as verified</button>
        </form>
      <?php endif; ?>

      <form method="post" action="<?= e(base_url('admin/users/' . $id . '/force-password-reset')) ?>"
            data-confirm="Email this user a password reset link?">
        <?= csrf_field() ?>
        <button class="btn btn-light" type="submit">Send a password reset link</button>
      </form>

      <?php if ((string) $user['status'] === 'active' && !$isSelf): ?>
        <form method="post" action="<?= e(base_url('admin/users/' . $id . '/suspend')) ?>" data-confirm="Suspend this account? They will be signed out and blocked.">
          <?= csrf_field() ?>
          <div class="field" style="margin:0">
            <label for="reason">Reason<span class="req">*</span></label>
            <input class="input" type="text" id="reason" name="reason" required minlength="5" maxlength="500"
                   placeholder="Repeated fraudulent fundraising requests">
            <span class="form-help">Included in the email we send them.</span>
          </div>
          <button class="btn btn-outline-brand" type="submit" style="border-color:#a3231b;color:#a3231b">Suspend account</button>
        </form>
      <?php elseif ((string) $user['status'] !== 'active'): ?>
        <form method="post" action="<?= e(base_url('admin/users/' . $id . '/reactivate')) ?>">
          <?= csrf_field() ?>
          <button class="btn btn-brand" type="submit">Reactivate account</button>
        </form>
      <?php endif; ?>
    </div>

    <?php endif; ?>
    <?php if ($canManageRoles): ?>
      <h3 class="mt-3" style="font-size:16px">Roles</h3>
      <form method="post" action="<?= e(base_url('admin/users/' . $id . '/roles')) ?>">
        <?= csrf_field() ?>
        <div class="choice-grid">
          <?php foreach ($availableRoles as $role): ?>
            <label class="choice">
              <input type="checkbox" name="roles[]" value="<?= e($role) ?>" <?= in_array($role, $currentRoles, true) ? 'checked' : '' ?>>
              <span><b><?= e(str_replace('_', ' ', ucfirst($role))) ?></b><em>
                <?php
                  echo match ($role) {
                      'super_admin' => 'Full access, including credentials and roles.',
                      'admin'       => 'Moderate fundraisers, donations and users.',
                      'initial_reviewer', 'initial_review' => 'First-pass donation verification; cannot approve own submission.',
                      'verification_officer' => 'Verify donation evidence at the second review stage.',
                      'finance_manager' => 'Finance review, reconciliation and reports.',
                      'content_manager' => 'Manage website content.',
                      'volunteer_manager' => 'Manage volunteer applications.',
                      'fundraiser'  => 'Create and manage their own fundraisers.',
                      default       => 'Donor account and receipts.',
                  };
                ?>
              </em></span>
            </label>
          <?php endforeach; ?>
        </div>
        <button class="btn btn-brand" type="submit">Save roles</button>
        <p class="form-help">The last super administrator cannot be demoted — promote somebody else first.</p>
      </form>
    <?php else: ?>
      <div class="alert alert-info mt-3">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 7.5h.01"/></svg>
        <p>Only a super administrator can change roles.</p>
      </div>
    <?php endif; ?>
  </section><?php endif; ?>
</div>

<?php if ($assignedGrants !== null): ?>
<section class="panel"><h2>Assigned grants</h2>
<?php if ($assignedGrants===[]): ?><p>No grants assigned to this user.</p><?php endif; ?>
<div class="table-wrap"><table class="data-table"><thead><tr><th>Grant</th><th>Assignment</th><th>Date</th><th>Responsibilities</th></tr></thead><tbody><?php foreach ($assignedGrants as $a): ?><tr><td><a href="<?= e(base_url('admin/grants/'.(int)$a['grant_id'])) ?>"><?= e($a['reference'].' · '.$a['title']) ?></a></td><td><?= e($a['status']) ?></td><td><?= e((string)$a['assigned_at']) ?> UTC</td><td><?= e((string)($a['responsibilities'] ?: '—')) ?></td></tr><?php endforeach; ?></tbody></table></div></section>
<?php endif; ?>
<?php if ($assignableGrants !== [] && (string)$user['status']==='active'): ?>
<section class="panel"><h2>Assign multiple grants</h2><p class="panel-sub">Select one or several open grants for this user. Duplicate assignments are rejected.</p><form method="post" action="<?= e(base_url('admin/users/'.$id.'/grants')) ?>"><?= csrf_field() ?><div class="field"><label for="grant_ids">Grants</label><select class="input" id="grant_ids" name="grant_ids[]" multiple size="8" required><?php foreach ($assignableGrants as $grant): if ($grant['status']==='closed') continue; ?><option value="<?= (int)$grant['id'] ?>"><?= e($grant['reference'].' · '.$grant['title']) ?></option><?php endforeach; ?></select></div><div class="field"><label for="grant_responsibilities">Responsibilities (optional)</label><textarea class="input" id="grant_responsibilities" name="responsibilities" maxlength="1000" rows="2"></textarea></div><div class="field"><label for="grant_notes">Notes (optional)</label><textarea class="input" id="grant_notes" name="notes" maxlength="10000" rows="2"></textarea></div><button class="btn btn-brand" type="submit">Assign grants</button></form></section>
<?php endif; ?>

<?php if ($fundraisers !== []): ?>
  <section class="panel">
    <h2>Fundraisers owned</h2>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Title</th><th>Status</th><th class="num">Raised</th><th class="num">Goal</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($fundraisers as $row): ?>
            <tr>
              <td class="wrap"><?= e((string) $row['title']) ?></td>
              <td><span class="<?= e(status_badge_class((string) $row['status'])) ?>"><?= e(str_replace('_', ' ', (string) $row['status'])) ?></span></td>
              <td class="num"><?= e(money_short((int) $row['raised_minor'])) ?></td>
              <td class="num"><?= e(money_short((int) $row['goal_minor'])) ?></td>
              <td><?php if ($can('reports.view')): ?><a class="btn btn-light btn-sm" href="<?= e(base_url('admin/reports/fundraisers?q=' . rawurlencode((string) $row['title']))) ?>">Find in report</a><?php else: ?><span class="muted">—</span><?php endif; ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
<?php endif; ?>

<?php if ($donations !== []): ?>
  <section class="panel">
    <h2>Donations from this donor</h2>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Reference</th><th>Date</th><th>Status</th><th class="num">Amount</th></tr></thead>
        <tbody>
          <?php foreach ($donations as $row): ?>
            <tr>
              <td class="mono"><?= e((string) $row['public_reference']) ?></td>
              <td><?= e(dt((string) $row['created_at'], 'j M Y')) ?></td>
              <td><span class="<?= e(status_badge_class((string) $row['status'])) ?>"><?= e((string) $row['status']) ?></span></td>
              <td class="num"><?= e(money((int) $row['amount_minor'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
<?php endif; ?>

<section class="panel">
  <h2>Activity</h2>
  <?php if ($auditTrail === []): ?>
    <p class="text-muted">Nothing recorded for this account yet.</p>
  <?php else: ?>
    <ul class="timeline">
      <?php foreach ($auditTrail as $entry): ?>
        <li>
          <span class="when"><?= e(dt((string) $entry['created_at'], 'j M Y H:i')) ?> UTC</span>
          <div class="what"><?= e((string) $entry['action']) ?></div>
          <div class="meta">
            <?php if (!empty($entry['entity_type'])): ?><?= e((string) $entry['entity_type']) ?> #<?= e((string) $entry['entity_id']) ?> &middot; <?php endif; ?>
            <?= e((string) ($entry['ip_address'] ?? '')) ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</section>
