<form class="site-form" method="post" action="<?= e(base_url('forms/contact')) ?>" data-ajax-form>
    <?php require __DIR__ . '/_guard.php'; ?>
    <div class="form-grid">
        <?php foreach ([
            ['name' => 'name', 'label' => 'Full name', 'required' => true, 'placeholder' => 'E.g. Ali Raza', 'autocomplete' => 'name', 'min' => 2, 'max' => 120],
            ['name' => 'email', 'label' => 'Email address', 'type' => 'email', 'required' => true, 'placeholder' => 'E.g. ali@example.com', 'autocomplete' => 'email', 'max' => 191],
            ['name' => 'phone', 'label' => 'Mobile/Phone number', 'type' => 'tel', 'required' => true, 'placeholder' => 'E.g. +92 300 400 5000', 'autocomplete' => 'tel', 'min' => 7, 'max' => 40],
            ['name' => 'subject', 'label' => 'Subject', 'required' => true, 'placeholder' => 'How can we help?', 'min' => 2, 'max' => 200],
            ['name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'required' => true, 'rows' => 5, 'placeholder' => 'Enter your message…', 'wide' => true, 'min' => 10, 'max' => 5000],
        ] as $f) { require __DIR__ . '/_field.php'; } ?>
    </div>
    <div class="form-feedback" role="status" aria-live="polite"><?php if ($m = flash('success')): ?><span class="ok"><?= e($m) ?></span><?php endif; ?></div>
    <div class="form-actions"><button type="submit" class="btn btn-primary">Send Message</button></div>
</form>
