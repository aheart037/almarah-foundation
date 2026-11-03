<?php $jobs = is_array($jobs ?? null) ? $jobs : []; ?>
<form class="site-form" method="post" action="<?= e(base_url('forms/career')) ?>" enctype="multipart/form-data" data-ajax-form novalidate id="apply">
    <?php require __DIR__ . '/_guard.php'; ?>
    <div class="form-grid">
        <?php
        $positionField = $jobs !== []
            ? ['name' => 'position', 'label' => 'Position', 'type' => 'select', 'required' => true, 'options' => array_merge(array_combine(array_column($jobs, 'title'), array_column($jobs, 'title')), ['General application' => 'General application']), 'placeholder' => 'Choose a role', 'wide' => true]
            : ['name' => 'position', 'label' => 'Position you are applying for', 'required' => true, 'placeholder' => 'E.g. Programme Coordinator — or "General application"', 'wide' => true, 'max' => 160];
        foreach ([
            $positionField,
            ['name' => 'name', 'label' => 'Full name', 'required' => true, 'placeholder' => 'E.g. Ali Raza', 'autocomplete' => 'name', 'max' => 120],
            ['name' => 'email', 'label' => 'Email address', 'type' => 'email', 'required' => true, 'placeholder' => 'E.g. ali@example.com', 'autocomplete' => 'email', 'max' => 191],
            ['name' => 'phone', 'label' => 'Phone number', 'type' => 'tel', 'required' => true, 'placeholder' => 'E.g. +92 300 400 5000', 'autocomplete' => 'tel', 'max' => 40],
            ['name' => 'city', 'label' => 'City', 'placeholder' => 'E.g. Lahore', 'max' => 120],
            ['name' => 'linkedin_url', 'label' => 'LinkedIn profile', 'type' => 'url', 'placeholder' => 'https://linkedin.com/in/…', 'max' => 255, 'wide' => true],
            ['name' => 'resume', 'label' => 'Upload CV (PDF or Word, max 5 MB)', 'type' => 'file', 'required' => true, 'accept' => '.pdf,.doc,.docx', 'wide' => true],
            ['name' => 'cover_letter', 'label' => 'Cover letter', 'type' => 'textarea', 'rows' => 5, 'placeholder' => 'Tell us why you would like to join Al Marah Foundation…', 'wide' => true, 'max' => 8000],
        ] as $f) { require __DIR__ . '/_field.php'; } ?>
    </div>
    <div class="form-feedback" role="status" aria-live="polite"><?php if ($m = flash('success')): ?><span class="ok"><?= e($m) ?></span><?php endif; ?></div>
    <div class="form-actions"><button type="submit" class="btn btn-primary">Submit Application</button></div>
</form>
