<?php
$countries = ['Pakistan' => 'Pakistan', 'United Kingdom' => 'United Kingdom', 'United States' => 'United States', 'United Arab Emirates' => 'United Arab Emirates', 'Saudi Arabia' => 'Saudi Arabia', 'Canada' => 'Canada', 'Australia' => 'Australia', 'Other' => 'Other'];
$occ = old('occupation_type', 'student');
?>
<form class="site-form volunteer-form" method="post" action="<?= e(base_url('forms/volunteer')) ?>" data-ajax-form novalidate id="volunteerForm">
    <?php require __DIR__ . '/_guard.php'; ?>
    <h3 class="form-section-title">Personal details</h3>
    <div class="form-grid">
        <?php foreach ([
            ['name' => 'full_name', 'label' => 'Your full name', 'required' => true, 'placeholder' => 'E.g. Ali Raza', 'autocomplete' => 'name', 'max' => 160],
            ['name' => 'guardian_name', 'label' => 'Parent/Guardian name', 'required' => true, 'placeholder' => 'E.g. Nazeer Ahmad', 'max' => 120],
            ['name' => 'cnic', 'label' => 'Your CNIC / B-Form', 'required' => true, 'placeholder' => 'xxxxx-xxxxxxx-x', 'max' => 20],
            ['name' => 'date_of_birth', 'label' => 'Date of birth', 'type' => 'date', 'required' => true, 'autocomplete' => 'bday'],
            ['name' => 'gender', 'label' => 'Gender', 'type' => 'radio', 'required' => true, 'options' => ['male' => 'Male', 'female' => 'Female', 'other' => 'Other'], 'wide' => true],
        ] as $f) { require __DIR__ . '/_field.php'; } ?>
    </div>
    <h3 class="form-section-title">Address</h3>
    <div class="form-grid">
        <?php foreach ([
            ['name' => 'street', 'label' => 'Street address', 'required' => true, 'placeholder' => 'E.g. 3 Canal Park, Gulberg-II', 'autocomplete' => 'address-line1', 'max' => 200],
            ['name' => 'area', 'label' => 'Area', 'placeholder' => 'Area / locality', 'autocomplete' => 'address-line2', 'max' => 50],
            ['name' => 'city', 'label' => 'City', 'required' => true, 'placeholder' => 'E.g. Lahore', 'autocomplete' => 'address-level2', 'max' => 120],
            ['name' => 'state', 'label' => 'State/Province', 'required' => true, 'placeholder' => 'E.g. Punjab', 'autocomplete' => 'address-level1', 'max' => 120],
            ['name' => 'country', 'label' => 'Country', 'type' => 'select', 'required' => true, 'options' => $countries, 'placeholder' => 'Select country', 'value' => 'Pakistan'],
        ] as $f) { require __DIR__ . '/_field.php'; } ?>
    </div>
    <h3 class="form-section-title">Contact</h3>
    <div class="form-grid">
        <?php foreach ([
            ['name' => 'email', 'label' => 'Email address', 'type' => 'email', 'required' => true, 'placeholder' => 'E.g. ali@example.com', 'autocomplete' => 'email', 'max' => 191],
            ['name' => 'phone', 'label' => 'Mobile number', 'type' => 'tel', 'required' => true, 'placeholder' => 'E.g. +92 300 400 5000', 'autocomplete' => 'tel', 'max' => 40],
            ['name' => 'whatsapp', 'label' => 'WhatsApp number', 'type' => 'tel', 'required' => true, 'placeholder' => 'E.g. +92 300 400 5000', 'max' => 40],
            ['name' => 'about', 'label' => 'Tell us a little about yourself', 'type' => 'textarea', 'rows' => 3, 'placeholder' => 'e.g., I work in marketing and enjoy community service', 'wide' => true, 'max' => 3000],
        ] as $f) { require __DIR__ . '/_field.php'; } ?>
    </div>
    <h3 class="form-section-title">Occupation</h3>
    <div class="form-grid">
        <?php $f = ['name' => 'occupation_type', 'label' => 'I am a', 'type' => 'radio', 'required' => true, 'options' => ['student' => 'Student', 'job' => 'Job Holder'], 'value' => $occ, 'wide' => true]; require __DIR__ . '/_field.php'; ?>
        <div class="form-grid field-wide" data-occupation="student"<?= $occ === 'job' ? ' hidden' : '' ?>>
            <?php foreach ([
                ['name' => 'institution', 'label' => 'College/University', 'required' => true, 'placeholder' => 'E.g. National University', 'max' => 160],
                ['name' => 'program', 'label' => 'Enrolled program', 'required' => true, 'placeholder' => 'E.g. Bachelor in Arts', 'max' => 160],
                ['name' => 'semester', 'label' => 'Current semester', 'required' => true, 'placeholder' => 'E.g. 1st', 'max' => 40],
            ] as $f) { require __DIR__ . '/_field.php'; } ?>
        </div>
        <div class="form-grid field-wide" data-occupation="job"<?= $occ !== 'job' ? ' hidden' : '' ?>>
            <?php foreach ([
                ['name' => 'organisation', 'label' => 'Organization', 'required' => true, 'placeholder' => 'E.g. Abc (Pvt) Ltd.', 'max' => 160],
                ['name' => 'qualification', 'label' => 'Qualification', 'required' => true, 'placeholder' => 'E.g. Bachelor in Arts', 'max' => 160],
            ] as $f) { require __DIR__ . '/_field.php'; } ?>
        </div>
    </div>
    <h3 class="form-section-title">Volunteering</h3>
    <div class="form-grid">
        <?php foreach ([
            ['name' => 'motivation', 'label' => 'Why do you want to volunteer with the Almarah Foundation?', 'type' => 'textarea', 'required' => true, 'rows' => 4, 'wide' => true, 'max' => 5000],
            ['name' => 'experience', 'label' => 'Please tell us your other volunteering experiences.', 'type' => 'textarea', 'required' => true, 'rows' => 3, 'wide' => true, 'max' => 5000],
            ['name' => 'skills', 'label' => 'What are your skills?', 'type' => 'textarea', 'required' => true, 'rows' => 3, 'wide' => true, 'max' => 500],
            ['name' => 'availability', 'label' => 'Days you are available', 'type' => 'checkbox', 'required' => true, 'options' => array_combine(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'], ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']), 'wide' => true],
        ] as $f) { require __DIR__ . '/_field.php'; } ?>
    </div>
    <div class="form-feedback" role="status" aria-live="polite"><?php if ($m = flash('success')): ?><span class="ok"><?= e($m) ?></span><?php endif; ?></div>
    <div class="form-actions"><button type="submit" class="btn btn-primary">Submit Registration</button></div>
</form>
