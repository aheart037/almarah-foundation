<form class="site-form" method="post" action="<?= e(base_url('forms/newsletter')) ?>" data-ajax-form novalidate>
    <?php require __DIR__ . '/_guard.php'; ?>
    <input type="hidden" name="source" value="<?= e($newsletterSource ?? 'newsletter-page') ?>">
    <div class="form-grid">
        <?php foreach ([
            ['name' => 'name', 'label' => 'Your name', 'placeholder' => 'E.g. Ali Raza', 'autocomplete' => 'name', 'max' => 120, 'wide' => true],
            ['name' => 'email', 'label' => 'Email address', 'type' => 'email', 'required' => true, 'placeholder' => 'E.g. ali@example.com', 'autocomplete' => 'email', 'max' => 191, 'wide' => true],
        ] as $f) { require __DIR__ . '/_field.php'; } ?>
    </div>
    <div class="form-feedback" role="status" aria-live="polite"></div>
    <div class="form-actions"><button type="submit" class="btn btn-primary">Subscribe</button></div>
    <p class="form-note">No spam, ever. Unsubscribe with one click from any email.</p>
</form>
