<?php
/**
 * Small field renderer used by the public forms.
 * $f = ['name','label','type'=>'text','required'=>bool,'placeholder','value','options'=>[],'rows'=>int,'hint','autocomplete','max']
 */
$f = $f ?? [];
$name = (string) $f['name'];
$type = (string) ($f['type'] ?? 'text');
$id = 'f-' . preg_replace('/[^a-z0-9_-]/i', '-', $name);
$err = error_for($name);
$val = old($name, (string) ($f['value'] ?? ''));
$req = !empty($f['required']);
$attrs = ($req ? ' required' : '') . (!empty($f['autocomplete']) ? ' autocomplete="' . e($f['autocomplete']) . '"' : '') . (!empty($f['max']) ? ' maxlength="' . (int) $f['max'] . '"' : '');
?>
<div class="field<?= $err !== '' ? ' has-error' : '' ?><?= !empty($f['wide']) ? ' field-wide' : '' ?>">
    <?php if ($type !== 'radio' && $type !== 'checkbox'): ?>
    <label for="<?= $id ?>"><?= e($f['label']) ?><?= $req ? ' <span class="req" aria-hidden="true">*</span>' : '' ?></label>
    <?php else: ?>
    <span class="label"><?= e($f['label']) ?><?= $req ? ' <span class="req" aria-hidden="true">*</span>' : '' ?></span>
    <?php endif; ?>
    <?php if ($type === 'textarea'): ?>
        <textarea id="<?= $id ?>" name="<?= e($name) ?>" rows="<?= (int) ($f['rows'] ?? 4) ?>" placeholder="<?= e($f['placeholder'] ?? '') ?>"<?= $attrs ?>><?= e($val) ?></textarea>
    <?php elseif ($type === 'select'): ?>
        <select id="<?= $id ?>" name="<?= e($name) ?>"<?= $attrs ?>>
            <option value=""><?= e($f['placeholder'] ?? 'Select…') ?></option>
            <?php foreach ((array) ($f['options'] ?? []) as $ov => $ol): ?>
            <option value="<?= e((string) $ov) ?>"<?= (string) $ov === $val ? ' selected' : '' ?>><?= e((string) $ol) ?></option>
            <?php endforeach; ?>
        </select>
    <?php elseif ($type === 'radio' || $type === 'checkbox'): ?>
        <div class="choice-row" role="group" aria-label="<?= e($f['label']) ?>">
            <?php $oldArr = $type === 'checkbox' ? (array) (((array) (flash('_old') ?? []))[$name] ?? []) : [$val]; ?>
            <?php foreach ((array) ($f['options'] ?? []) as $ov => $ol): ?>
            <label class="choice"><input type="<?= $type ?>" name="<?= e($name) ?><?= $type === 'checkbox' ? '[]' : '' ?>" value="<?= e((string) $ov) ?>"<?= in_array((string) $ov, array_map('strval', $oldArr), true) ? ' checked' : '' ?><?= $req && $type === 'radio' ? ' required' : '' ?>> <span><?= e((string) $ol) ?></span></label>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <input id="<?= $id ?>" type="<?= e($type) ?>" name="<?= e($name) ?>" value="<?= e($val) ?>" placeholder="<?= e($f['placeholder'] ?? '') ?>"<?= $attrs ?><?= $type === 'file' && !empty($f['accept']) ? ' accept="' . e($f['accept']) . '"' : '' ?>>
    <?php endif; ?>
    <?php if (!empty($f['hint'])): ?><div class="hint"><?= e($f['hint']) ?></div><?php endif; ?>
    <?php if ($err !== ''): ?><div class="error"><?= e($err) ?></div><?php endif; ?>
</div>
