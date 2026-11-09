<?php
/** @var list<array> $roleSummaries @var bool $canAssignRoles */
$oldInput = _flash_memo('_old', []);
$picked = is_array($oldInput) && is_array($oldInput['roles'] ?? null) ? $oldInput['roles'] : [];
$mode = old('password_mode', 'set') === 'email' ? 'email' : 'set';
?>
<link rel="stylesheet" href="<?= e(asset('assets/css/admin-users.css')) ?>">
<div class="dash-head">
  <div><h1>Create user</h1><p class="sub">Set the password yourself or email a setup link, choose the role, and see exactly what each role can do. The user can change the password later with <em>Forgot password</em>.</p></div>
  <a class="btn btn-light" href="<?= e(base_url('admin/users')) ?>">Back</a>
</div>
<form method="post" action="<?= e(base_url('admin/users')) ?>" class="uc-wrap" autocomplete="off"><?= csrf_field() ?>
  <?php if (error_for('form') !== ''): ?><div class="uc-alert" role="alert"><?= error_for('form') ?></div><?php endif; ?>

  <section class="panel">
    <h2 style="margin-top:0">1. Account details</h2>
    <div class="uc-grid">
      <div class="uc-field"><label for="first_name">First name *</label><input class="input" id="first_name" name="first_name" maxlength="100" required value="<?= old('first_name') ?>"></div>
      <div class="uc-field"><label for="last_name">Last name *</label><input class="input" id="last_name" name="last_name" maxlength="100" required value="<?= old('last_name') ?>"></div>
      <div class="uc-field"><label for="email">Email (login) *</label><input class="input" type="email" id="email" name="email" maxlength="191" required value="<?= old('email') ?>"></div>
      <div class="uc-field"><label for="phone">Phone</label><input class="input" id="phone" name="phone" maxlength="40" value="<?= old('phone') ?>"></div>
    </div>
  </section>

  <section class="panel">
    <h2 style="margin-top:0">2. Password</h2>
    <div class="uc-modes">
      <label><input type="radio" name="password_mode" value="set" <?= $mode === 'set' ? 'checked' : '' ?>><span>Set the password now<small>You type a password and share it with the user.</small></span></label>
      <label><input type="radio" name="password_mode" value="email" <?= $mode === 'email' ? 'checked' : '' ?>><span>Email a setup link<small>The user chooses their own password.</small></span></label>
    </div>
    <div class="uc-grid" id="pwFields">
      <div class="uc-field uc-pw"><label for="password">Password</label><input class="input" type="password" id="password" name="password" maxlength="200" autocomplete="new-password"><button type="button" data-show="password">Show</button><div class="hint">At least 10 characters with letters and numbers.</div></div>
      <div class="uc-field uc-pw"><label for="password_confirmation">Confirm password</label><input class="input" type="password" id="password_confirmation" name="password_confirmation" maxlength="200" autocomplete="new-password"><button type="button" data-show="password_confirmation">Show</button></div>
    </div>
  </section>

  <section class="panel">
    <h2 style="margin-top:0">3. Role</h2>
    <p class="sub" style="margin-top:0">Tick the role(s) for this user. The table shows what each role is allowed to do.</p>
    <?php if (!$canAssignRoles): ?><div class="uc-lock">Only a Super Admin can assign roles. You can see what each role allows, but the user will be created without a role.</div><?php endif; ?>
    <table class="uc-roles">
      <thead><tr><th>Role</th><th>Privileges this role has</th></tr></thead>
      <tbody>
      <?php foreach ($roleSummaries as $r): $on = in_array($r['name'], $picked, true); ?>
        <tr class="<?= $on ? 'is-picked' : '' ?>">
          <td><label class="uc-role-pick"><input type="checkbox" name="roles[]" value="<?= e($r['name']) ?>" <?= $on ? 'checked' : '' ?> <?= $canAssignRoles ? '' : 'disabled' ?>>
            <span><strong><?= e($r['label']) ?></strong><?php if ($r['description'] !== ''): ?><small><?= e($r['description']) ?></small><?php endif; ?></span></label></td>
          <td>
            <?php if ($r['all']): ?><span class="uc-all">Full access to everything</span>
            <?php elseif (!$r['groups']): ?><span class="uc-none">No privileges assigned yet.</span>
            <?php else: foreach ($r['groups'] as $group => $labels): ?>
              <div class="uc-grp"><b><?= e(ucwords(str_replace(['_', '.'], ' ', (string) $group))) ?></b>
                <?php foreach ($labels as $label): ?><span class="uc-chip"><?= e($label) ?></span><?php endforeach; ?></div>
            <?php endforeach; endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </section>

  <div><button class="btn btn-brand admin-user-create-submit" type="submit">Create user</button></div>
</form>
<script>
(function () {
  var radios = document.querySelectorAll('input[name=password_mode]'), box = document.getElementById('pwFields');
  function sync() { var m = document.querySelector('input[name=password_mode]:checked').value; box.style.display = m === 'set' ? '' : 'none'; box.querySelectorAll('input').forEach(function (i) { i.required = m === 'set'; }); }
  radios.forEach(function (r) { r.addEventListener('change', sync); }); sync();
  document.querySelectorAll('[data-show]').forEach(function (b) { b.addEventListener('click', function () { var i = document.getElementById(b.dataset.show); i.type = i.type === 'password' ? 'text' : 'password'; b.textContent = i.type === 'password' ? 'Show' : 'Hide'; }); });
  document.querySelectorAll('.uc-roles input[type=checkbox]').forEach(function (c) { c.addEventListener('change', function () { c.closest('tr').classList.toggle('is-picked', c.checked); }); });
})();
</script>
