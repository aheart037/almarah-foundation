<form class="site-form" method="post" action="<?= e(base_url('forms/contact')) ?>" data-ajax-form novalidate>
    <?php require __DIR__ . '/_guard.php'; ?>
    <div class="form-grid">
        <?php foreach ([
            ['name' => 'name', 'label' => 'Your name', 'required' => true, 'placeholder' => 'E.g. Ali Raza', 'autocomplete' => 'name', 'max' => 120],
            ['name' => 'email', 'label' => 'Email address', 'type' => 'email', 'required' => true, 'placeholder' => 'E.g. ali@example.com', 'autocomplete' => 'email', 'max' => 191],
            ['name' => 'phone', 'label' => 'Phone number', 'type' => 'tel', 'placeholder' => 'E.g. +92 300 400 5000', 'autocomplete' => 'tel', 'max' => 40],
            ['name' => 'subject', 'label' => 'Subject', 'placeholder' => 'How can we help?', 'max' => 200],
            ['name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'required' => true, 'rows' => 5, 'placeholder' => 'Enter your message…', 'wide' => true, 'max' => 5000],
        ] as $f) { require __DIR__ . '/_field.php'; } ?>
    </div>
    <div class="form-feedback" role="status" aria-live="polite"><?php if ($m = flash('success')): ?><span class="ok"><?= e($m) ?></span><?php endif; ?></div>
    <div class="form-actions"><button type="submit" class="btn btn-primary">Send Message</button></div>
</form>
