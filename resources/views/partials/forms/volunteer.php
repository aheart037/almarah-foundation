<?php $occ = old('occupation_type', 'student'); ?>
<form class="site-form volunteer-form" method="post" action="<?= e(base_url('forms/volunteer')) ?>" data-ajax-form id="volunteerForm">
    <?php require __DIR__ . '/_guard.php'; ?>
    <h3 class="form-section-title">About you</h3>
    <div class="form-grid">
        <?php foreach ([
            ['name'=>'full_name','label'=>'Full name','required'=>true,'autocomplete'=>'name','min'=>3,'max'=>160],
            ['name'=>'email','label'=>'Email address','type'=>'email','required'=>true,'autocomplete'=>'email','max'=>191],
            ['name'=>'phone','label'=>'Mobile number','type'=>'tel','required'=>true,'autocomplete'=>'tel','min'=>7,'max'=>40],
            ['name'=>'whatsapp','label'=>'WhatsApp number (if different)','type'=>'tel','max'=>40],
            ['name'=>'city','label'=>'City','required'=>true,'autocomplete'=>'address-level2','max'=>120],
            ['name'=>'street','label'=>'Street address','required'=>true,'autocomplete'=>'street-address','max'=>200,'wide'=>true],
        ] as $f) { require __DIR__ . '/_field.php'; } ?>
    </div>
    <h3 class="form-section-title">Occupation</h3>
    <div class="form-grid">
        <?php $f = ['name'=>'occupation_type','label'=>'I am a','type'=>'radio','required'=>true,'value'=>$occ,'options'=>['student'=>'Student','job'=>'Employed','other'=>'Other'],'wide'=>true]; require __DIR__ . '/_field.php'; ?>
        <div class="form-grid field-wide" data-occupation="student"<?= $occ !== 'student' ? ' hidden' : '' ?>>
            <?php $f = ['name'=>'institution','label'=>'College / university','max'=>160]; require __DIR__ . '/_field.php'; ?>
        </div>
        <div class="form-grid field-wide" data-occupation="job"<?= $occ !== 'job' ? ' hidden' : '' ?>>
            <?php $f = ['name'=>'organisation','label'=>'Organisation','max'=>160]; require __DIR__ . '/_field.php'; ?>
        </div>
        <div class="form-grid field-wide" data-occupation="other"<?= $occ !== 'other' ? ' hidden' : '' ?>>
            <?php $f = ['name'=>'occupation_detail','label'=>'Please describe your occupation','required'=>true,'max'=>120]; require __DIR__ . '/_field.php'; ?>
        </div>
    </div>
    <h3 class="form-section-title">Volunteering interests</h3>
    <div class="form-grid">
        <?php foreach ([
            ['name'=>'skills','label'=>'Skills','required'=>true,'type'=>'textarea','rows'=>3,'min'=>2,'max'=>500,'wide'=>true],
            ['name'=>'areas_of_interest','label'=>'Areas of interest','type'=>'checkbox','required'=>true,'options'=>['education'=>'Education','food'=>'Food relief','community'=>'Community','fundraising'=>'Fundraising','events'=>'Events','other'=>'Other'],'wide'=>true],
            ['name'=>'availability','label'=>'Days available','type'=>'checkbox','required'=>true,'options'=>array_combine(['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'],['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday']),'wide'=>true],
            ['name'=>'experience','label'=>'Previous volunteer experience','type'=>'textarea','rows'=>3,'max'=>5000,'wide'=>true],
            ['name'=>'motivation','label'=>'Why would you like to volunteer?','type'=>'textarea','required'=>true,'rows'=>4,'min'=>10,'max'=>5000,'wide'=>true],
            ['name'=>'additional_notes','label'=>'Additional notes (optional)','type'=>'textarea','rows'=>3,'max'=>3000,'wide'=>true],
        ] as $f) { require __DIR__ . '/_field.php'; } ?>
    </div>
    <div class="form-feedback" role="status" aria-live="polite"><?php if ($m = flash('success')): ?><span class="ok"><?= e($m) ?></span><?php endif; ?></div>
    <div class="form-actions"><button type="submit" class="btn btn-primary">Submit Registration</button></div>
</form>

<script>
(function () {
  var form = document.getElementById('volunteerForm');
  if (!form) return;
  ['availability', 'areas_of_interest'].forEach(function (name) {
    var choices = Array.prototype.slice.call(form.querySelectorAll('input[name="' + name + '[]"]'));
    if (!choices.length) return;
    function validate() {
      choices[0].setCustomValidity(choices.some(function (c) { return c.checked; }) ? '' : 'Please select at least one option.');
    }
    choices.forEach(function (c) { c.addEventListener('change', validate); });
    form.addEventListener('reset', function () { setTimeout(validate, 0); });
    validate();
  });
}());
</script>
