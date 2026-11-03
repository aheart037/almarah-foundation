<?php
/**
 * Generic create / edit form driven by ResourceRegistry.
 *
 * @var array      $def
 * @var array|null $row
 * @var array      $revisions
 */
$isEdit = is_array($row);
$base = base_url('admin/cms/' . $def['key']);
$action = $isEdit ? $base . '/' . (int) $row['id'] : $base;
$prefill = $prefill ?? [];
$val = static function (string $key, array $field) use ($row, $isEdit, $prefill): string {
    $default = $isEdit ? (string) ($row[$key] ?? '') : (string) ($prefill[$key] ?? $field['default'] ?? '');
    return old($key, $default);
};
?>
<div class="dash-head">
  <div>
    <h1><?= $isEdit ? 'Edit ' . e($def['singular']) : 'New ' . e($def['singular']) ?></h1>
    <p class="sub"><?= e($def['label']) ?> · changes go live as soon as you save.</p>
  </div>
  <div class="sr-actions">
    <?php if ($isEdit && !empty($def['preview']) && !empty($row['slug'])): ?>
      <a class="btn btn-light" href="<?= e(base_url(ltrim(str_replace('{slug}', (string) $row['slug'], $def['preview']), '/'))) ?>" target="_blank" rel="noopener">View page</a>
    <?php endif; ?>
    <a class="btn btn-light" href="<?= e($base) ?>">Back to list</a>
  </div>
</div>

<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" class="panel rs-form" novalidate>
  <?= csrf_field() ?>
  <div class="rs-grid">
  <?php foreach ($def['fields'] as $key => $field):
      $type = $field['type'];
      $err = error_for($key);
      $value = $val($key, $field);
      $half = !empty($field['half']);
      $required = str_contains((string) ($field['rules'] ?? ''), 'required') || (!empty($field['required_on_create']) && !$isEdit);
  ?>
    <div class="field <?= $half ? 'rs-half' : 'rs-full' ?> <?= $err !== '' ? 'is-invalid' : '' ?>" data-type="<?= e($type) ?>">
      <?php if ($type === 'bool'): ?>
        <label class="rs-switch">
          <input type="hidden" name="<?= e($key) ?>" value="0">
          <input type="checkbox" name="<?= e($key) ?>" value="1" <?= $value === '1' ? 'checked' : '' ?>>
          <span><?= e($field['label']) ?></span>
        </label>
      <?php else: ?>
        <label for="f_<?= e($key) ?>"><?= e($field['label']) ?><?= $required ? '<span class="req">*</span>' : '' ?></label>

        <?php if ($type === 'textarea' || $type === 'json'): ?>
          <textarea class="textarea <?= $type === 'json' ? 'mono' : '' ?>" id="f_<?= e($key) ?>" name="<?= e($key) ?>" rows="<?= (int) ($field['rows'] ?? 4) ?>" <?= isset($field['counter']) ? 'data-counter="' . (int) $field['counter'] . '"' : '' ?>><?= e($value) ?></textarea>

        <?php elseif ($type === 'html'): ?>
          <div class="rs-editor" data-editor>
            <div class="rs-editor-bar" role="toolbar" aria-label="Formatting">
              <button type="button" data-cmd="formatBlock" data-arg="h2">H2</button>
              <button type="button" data-cmd="formatBlock" data-arg="h3">H3</button>
              <button type="button" data-cmd="formatBlock" data-arg="p">¶</button>
              <span class="sep"></span>
              <button type="button" data-cmd="bold"><b>B</b></button>
              <button type="button" data-cmd="italic"><i>I</i></button>
              <button type="button" data-cmd="insertUnorderedList">• List</button>
              <button type="button" data-cmd="insertOrderedList">1. List</button>
              <button type="button" data-cmd="createLink">Link</button>
              <button type="button" data-cmd="removeFormat">Clear</button>
              <span class="sep"></span>
              <button type="button" data-toggle-source>HTML</button>
            </div>
            <div class="rs-editor-area" contenteditable="true"><?= $value /* trusted staff HTML, scripts stripped on save */ ?></div>
            <textarea class="textarea mono rs-editor-source" id="f_<?= e($key) ?>" name="<?= e($key) ?>" rows="14" hidden><?= e($value) ?></textarea>
          </div>

        <?php elseif ($type === 'select'): ?>
          <select class="select-field" id="f_<?= e($key) ?>" name="<?= e($key) ?>">
            <?php foreach ($field['options'] as $optValue => $optLabel): ?>
              <option value="<?= e((string) $optValue) ?>" <?= $value === (string) $optValue ? 'selected' : '' ?>><?= e($optLabel) ?></option>
            <?php endforeach; ?>
          </select>

        <?php elseif ($type === 'image' || $type === 'pdf'): ?>
          <?php $current = $isEdit ? (string) ($row[$key] ?? '') : ''; ?>
          <div class="rs-upload">
            <?php if ($current !== ''): ?>
              <div class="rs-upload-current">
                <?php if ($type === 'image'): ?>
                  <img src="<?= e(base_url($current)) ?>" alt="" class="rs-preview">
                <?php else: ?>
                  <a href="<?= e(base_url($current)) ?>" target="_blank" rel="noopener">Current file</a>
                <?php endif; ?>
                <label class="rs-remove"><input type="checkbox" name="<?= e($key) ?>_remove" value="1"> Remove</label>
              </div>
            <?php endif; ?>
            <input class="input" type="file" id="f_<?= e($key) ?>" name="<?= e($key) ?>" accept="<?= $type === 'image' ? 'image/jpeg,image/png,image/webp,image/gif' : 'application/pdf' ?>" data-preview>
            <img class="rs-preview rs-preview-new" alt="" hidden>
          </div>

        <?php elseif ($type === 'color'): ?>
          <div class="rs-color">
            <input type="color" id="f_<?= e($key) ?>_pick" value="<?= e($value !== '' ? $value : '#6b0f35') ?>" aria-label="Pick colour">
            <input class="input mono" type="text" id="f_<?= e($key) ?>" name="<?= e($key) ?>" value="<?= e($value) ?>" maxlength="9" pattern="#[0-9a-fA-F]{3,8}">
          </div>

        <?php else: ?>
          <?php
            $inputType = match ($type) {
                'int', 'decimal' => 'number',
                'date'           => 'date',
                'datetime'       => 'datetime-local',
                'url'            => 'url',
                default          => 'text',
            };
            if ($type === 'datetime' && $value !== '') {
                $value = str_replace(' ', 'T', substr($value, 0, 16));
            }
            if ($type === 'date' && $value !== '') {
                $value = substr($value, 0, 10);
            }
          ?>
          <input class="input <?= $type === 'slug' ? 'mono' : '' ?>" type="<?= $inputType ?>" id="f_<?= e($key) ?>" name="<?= e($key) ?>" value="<?= e($value) ?>"
                 <?= $type === 'slug' && !empty($field['from']) ? 'data-slug-from="f_' . e($field['from']) . '"' : '' ?>
                 <?= $type === 'decimal' ? 'step="0.01"' : '' ?>
                 <?= isset($field['counter']) ? 'data-counter="' . (int) $field['counter'] . '"' : '' ?>>
        <?php endif; ?>
      <?php endif; ?>

      <?php if (!empty($field['help'])): ?><span class="form-help"><?= e($field['help']) ?></span><?php endif; ?>
      <?php if ($err !== ''): ?><span class="field-error"><?= e($err) ?></span><?php endif; ?>
    </div>
  <?php endforeach; ?>
  </div>

  <div class="rs-actions">
    <button class="btn btn-brand" type="submit">Save</button>
    <button class="btn btn-light" type="submit" name="_stay" value="1">Save &amp; keep editing</button>
    <a class="btn btn-light" href="<?= e($base) ?>">Cancel</a>
  </div>
</form>

<?php if ($isEdit && $revisions !== []): ?>
<section class="panel" style="margin-top:20px">
  <div class="panel-head"><h3>Previous versions</h3><p class="panel-sub">Restoring saves the current version first, so nothing is lost.</p></div>
  <div class="table-wrap">
    <table class="data-table">
      <thead><tr><th>Saved</th><th>By</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($revisions as $rev): ?>
          <tr>
            <td><?= e(dt((string) $rev['created_at'], 'j M Y H:i')) ?></td>
            <td><?= e(trim((string) ($rev['first_name'] ?? '') . ' ' . (string) ($rev['last_name'] ?? '')) ?: 'System') ?></td>
            <td style="text-align:right">
              <form method="post" action="<?= e($base . '/' . (int) $row['id'] . '/revisions/' . (int) $rev['id'] . '/restore') ?>" class="inline-form" data-confirm="Restore this version?">
                <?= csrf_field() ?>
                <button class="btn btn-light btn-sm" type="submit">Restore</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
<?php endif; ?>
