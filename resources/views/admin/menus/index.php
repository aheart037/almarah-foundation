<?php
/** @var string $location @var array $locations @var array $tree @var int $maxDepth */
?>
<div class="dash-head">
  <div>
    <h1>Menus</h1>
    <p class="sub">Links in the header and footer. Page addresses are relative (e.g. <code>about</code>, <code>donate</code>); external links start with https://.</p>
  </div>
</div>

<div class="inbox-tabs">
  <?php foreach ($locations as $key => $label): ?>
    <a href="<?= e(base_url('admin/menus?location=' . $key)) ?>" class="<?= $key === $location ? 'active' : '' ?>"><?= e($label) ?></a>
  <?php endforeach; ?>
</div>

<section class="panel">
  <form method="post" action="<?= e(base_url('admin/menus/' . $location)) ?>" id="menuForm">
    <?= csrf_field() ?>
    <input type="hidden" name="tree" id="menuTree" value="<?= e(json_encode($tree, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '[]') ?>">
    <div class="menu-editor" data-menu-editor="menuTree" data-max-depth="<?= (int) $maxDepth ?>"></div>
    <div class="rs-actions">
      <button type="button" class="btn btn-light" data-menu-add>Add top-level item</button>
      <button type="submit" class="btn btn-brand">Save menu</button>
    </div>
  </form>
  <form method="post" action="<?= e(base_url('admin/menus/' . $location . '/reset')) ?>" style="margin-top:14px" data-confirm="Discard your custom menu and restore the built-in default?">
    <?= csrf_field() ?>
    <button class="btn btn-light btn-sm" type="submit">Restore default</button>
  </form>
</section>
