<?php
/**
 * Toolbar shown above a table that sits inside a <form data-bulk>. The form tag itself is written by the
 * including page so it can choose the action URL.
 * @var string $bulkNoun e.g. "donations"
 */
?>
<div class="bulk-bar" style="display:flex;align-items:center;gap:12px;margin:0 0 10px;flex-wrap:wrap">
  <input class="input" name="reason" required minlength="5" maxlength="500" placeholder="Reason for deleting (required)" aria-label="Reason for deleting" style="max-width:340px;min-height:34px;padding:6px 10px">
  <button class="btn btn-danger btn-sm" type="submit" data-bulk-submit disabled>Delete selected</button>
  <span class="muted" data-bulk-count style="font-size:13px"></span>
  <span class="muted" style="font-size:12px;margin-left:auto">Tick the <?= e($bulkNoun) ?> to remove. Greyed-out boxes cannot be deleted.</span>
</div>
