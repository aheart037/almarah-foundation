<?php
/**
 * Sign-in page: Almarah Foundation portal.
 *
 * @var string $intended
 * @var bool   $resetDone
 */
$emailError    = error_for('email');
$passwordError = error_for('password');
$oldEmail      = (string) old('email');
$focusPassword = $oldEmail !== '' && $emailError === '';
$tick = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg>';
?>
<link rel="stylesheet" href="<?= e(asset('assets/css/auth-foundation.css')) ?>">
<section class="auth-shell auth-shell--split">
  <div class="auth-stage">
    <div class="auth-card--split">

      <aside class="auth-art" aria-label="About Almarah Foundation">
        <div class="art-copy">
          <p class="art-eyebrow">Almarah Foundation</p>
          <h2>Welcome back</h2>
          <p>Sign in to the Almarah Foundation portal to manage donations, review payments and keep our work for orphaned and vulnerable children running smoothly.</p>

          <ul class="checkline">
            <li><?= $tick ?><span>Donations, receipts and certificates in one place</span></li>
            <li><?= $tick ?><span>Reports and verified payment records</span></li>
            <li><?= $tick ?><span>Access limited to your role, kept secure</span></li>
          </ul>

          <ul class="prog-pills" aria-label="Our programmes">
            <li>Parent the Orphan</li><li>Feed the Hungry</li><li>Izzat Ki Roti</li><li>Educate Pakistan</li>
          </ul>
        </div>
        <p class="art-foot">Every Child Deserves a Place to Call “Apna Ghar”.</p>
      </aside>

      <div class="auth-form-wrap">
        <div class="auth-form">
          <h1>Sign in</h1>
          <p class="auth-lede">Use the email address linked to your Almarah Foundation account.</p>

          <?php if ($resetDone): ?>
            <p class="auth-flash" role="status">Your password was reset. Sign in with the new one.</p>
          <?php endif; ?>

          <form method="post" action="<?= e(base_url('login')) ?>" id="login-form" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="next" value="<?= e($intended) ?>">

            <div class="field <?= $emailError !== '' ? 'is-invalid' : '' ?>">
              <label for="email">Email address<span class="req">*</span></label>
              <input class="input" type="email" id="email" name="email" required
                     <?= $focusPassword ? '' : 'autofocus' ?>
                     autocomplete="email" autocapitalize="off" spellcheck="false" inputmode="email"
                     placeholder="you@example.com" value="<?= e($oldEmail) ?>"
                     <?= $emailError !== '' ? 'aria-invalid="true" aria-describedby="email-error"' : '' ?>>
              <?php if ($emailError !== ''): ?>
                <span class="field-error" id="email-error" role="alert"><?= e($emailError) ?></span>
              <?php endif; ?>
            </div>

            <div class="field <?= $passwordError !== '' ? 'is-invalid' : '' ?>">
              <div class="label-row">
                <label for="password">Password<span class="req">*</span></label>
                <a class="link-quiet" href="<?= e(base_url('forgot-password')) ?>">Forgot password?</a>
              </div>
              <div class="input-group">
                <input class="input" type="password" id="password" name="password" required
                       <?= $focusPassword ? 'autofocus' : '' ?>
                       autocomplete="current-password" placeholder="Your password"
                       aria-describedby="<?= $passwordError !== '' ? 'password-error ' : '' ?>caps-hint"
                       <?= $passwordError !== '' ? 'aria-invalid="true"' : '' ?>>
                <button type="button" class="pw-toggle" data-toggle-password="#password" aria-label="Show password" aria-pressed="false">
                  <svg class="icon-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                  <svg class="icon-eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><path d="M1 1l22 22"/></svg>
                </button>
              </div>
              <span class="field-hint" id="caps-hint" role="status" hidden>Caps Lock is on.</span>
              <?php if ($passwordError !== ''): ?>
                <span class="field-error" id="password-error" role="alert"><?= e($passwordError) ?></span>
              <?php endif; ?>
            </div>

            <div class="form-actions">
              <button class="btn btn-brand btn-lg" type="submit" id="login-submit"><span class="btn-label">Sign in</span></button>
            </div>
          </form>

          <div class="divider-or"><span>need help?</span></div>

          <p class="auth-alt">
            Accounts are created by Almarah Foundation administrators.<br>
            Need access? <a href="<?= e(base_url('contact')) ?>">Contact our team</a>
          </p>

          <p class="auth-note">
            Just want to help a child? You don’t need an account.
            <a href="<?= e(base_url('donate')) ?>">Donate now</a> or <a href="<?= e(base_url()) ?>/">return to the website</a>.
          </p>
        </div>
      </div>

    </div>
  </div>
</section>

<script>
(function () {
  document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
    var input = document.querySelector(btn.getAttribute('data-toggle-password'));
    if (!input) return;
    btn.addEventListener('click', function () {
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.setAttribute('aria-pressed', String(show));
      btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
      input.focus({ preventScroll: true });
    });
  });
  var pw = document.getElementById('password'), hint = document.getElementById('caps-hint');
  if (pw && hint) {
    var check = function (e) { if (e.getModifierState) hint.hidden = !e.getModifierState('CapsLock'); };
    pw.addEventListener('keydown', check);
    pw.addEventListener('keyup', check);
    pw.addEventListener('blur', function () { hint.hidden = true; });
  }
  var form = document.getElementById('login-form'), btn = document.getElementById('login-submit');
  if (form && btn) {
    var label = btn.querySelector('.btn-label');
    form.addEventListener('submit', function () {
      if (btn.disabled) return;
      btn.disabled = true; btn.classList.add('is-loading'); label.textContent = 'Signing in\u2026';
    });
    window.addEventListener('pageshow', function (e) {
      if (e.persisted) { btn.disabled = false; btn.classList.remove('is-loading'); label.textContent = 'Sign in'; }
    });
  }
})();
</script>
