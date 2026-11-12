<?php
/** Request a password reset link: Almarah Foundation portal. */
$emailError = (string) error_for('email');
?>
<link rel="stylesheet" href="<?= e(asset('assets/css/auth-foundation.css')) ?>">
<section class="auth-shell auth-shell--split">
  <div class="auth-stage">
    <div class="auth-card--split">

      <aside class="auth-art" aria-label="How password recovery works">
        <div class="art-copy">
          <p class="art-eyebrow">Almarah Foundation</p>
          <h2>Locked out? It happens.</h2>
          <p>Tell us the email on your account and we will send you a secure link to choose a new password.</p>
          <ol class="steps">
            <li><span class="step-dot" aria-hidden="true">1</span><span>Enter your email address</span></li>
            <li><span class="step-dot" aria-hidden="true">2</span><span>Open the link we email you</span></li>
            <li><span class="step-dot" aria-hidden="true">3</span><span>Choose a new password and sign in</span></li>
          </ol>
        </div>
        <p class="art-foot">The link works for two hours and can be used once.</p>
      </aside>

      <div class="auth-form-wrap">
        <div class="auth-form">
          <h1>Reset your password</h1>
          <p class="auth-lede">We will email you a link that expires in two hours.</p>

          <form method="post" action="<?= e(base_url('forgot-password')) ?>" id="forgot-form" novalidate>
            <?= csrf_field() ?>
            <div class="field <?= $emailError !== '' ? 'is-invalid' : '' ?>">
              <label for="email">Email address<span class="req">*</span></label>
              <input class="input" type="email" id="email" name="email" required autofocus
                     autocomplete="email" autocapitalize="off" spellcheck="false" inputmode="email"
                     placeholder="you@example.com" value="<?= e(old('email')) ?>"
                     aria-describedby="<?= $emailError !== '' ? 'email-error ' : '' ?>privacy-note"
                     <?= $emailError !== '' ? 'aria-invalid="true"' : '' ?>>
              <?php if ($emailError !== ''): ?>
                <span class="field-error" id="email-error" role="alert"><?= e($emailError) ?></span>
              <?php endif; ?>
            </div>
            <div class="form-actions">
              <button class="btn btn-brand btn-lg" type="submit" id="forgot-submit"><span class="btn-label">Email me a reset link</span></button>
            </div>
          </form>

          <p class="privacy-note" id="privacy-note">
            For your security, we show the same message whether or not the address is registered.
            If nothing arrives within a few minutes, check your spam folder or <a href="<?= e(base_url('contact')) ?>">contact us</a>.
          </p>

          <div class="divider-or"><span>or</span></div>
          <p class="auth-alt"><a href="<?= e(base_url('login')) ?>">Back to sign in</a></p>
        </div>
      </div>

    </div>
  </div>
</section>

<script>
(function () {
  var form = document.getElementById('forgot-form'), btn = document.getElementById('forgot-submit');
  if (!form || !btn) return;
  var label = btn.querySelector('.btn-label'), original = label.textContent;
  form.addEventListener('submit', function () {
    if (btn.disabled) return;
    btn.disabled = true; btn.classList.add('is-loading'); label.textContent = 'Sending\u2026';
  });
  window.addEventListener('pageshow', function (e) {
    if (e.persisted) { btn.disabled = false; btn.classList.remove('is-loading'); label.textContent = original; }
  });
})();
</script>
