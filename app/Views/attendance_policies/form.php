<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$isEdit   = $policy !== null;
$val      = static fn (string $key, $default = '') => esc(old($key, $policy[$key] ?? $default));
$selected = (int) old('company_id', $policy['company_id'] ?? $preselect ?? 0);
$before   = db_bool(old('absent_before_holiday_forfeits_pay', $policy['absent_before_holiday_forfeits_pay'] ?? false));
$after    = db_bool(old('absent_after_holiday_forfeits_pay', $policy['absent_after_holiday_forfeits_pay'] ?? false));
?>

<div class="page-head">
  <div>
    <h1><?= $isEdit ? 'Edit attendance policy' : 'Add attendance policy' ?></h1>
    <p class="sub"><a href="<?= site_url('attendance-policies') ?>">&larr; Back to attendance policies</a></p>
  </div>
</div>

<div class="form-card">
  <form method="post" action="<?= $isEdit ? site_url('attendance-policies/' . $policy['id']) : site_url('attendance-policies') ?>">
    <?= csrf_field() ?>
    <div class="form-grid">
      <div>
        <label for="company_id">Company *</label>
        <?php if (count($companies) === 1): ?>
          <input type="hidden" name="company_id" value="<?= esc($companies[0]['id']) ?>">
          <input type="text" value="<?= esc($companies[0]['name']) ?>" disabled>
        <?php else: ?>
          <select id="company_id" name="company_id" required>
            <option value="">Choose a company&hellip;</option>
            <?php foreach ($companies as $c): ?>
              <option value="<?= esc($c['id']) ?>" <?= $selected === (int) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        <?php endif; ?>
      </div>
      <div>
        <label for="name">Policy name *</label>
        <input type="text" id="name" name="name" value="<?= $val('name') ?>" placeholder="e.g. Standard attendance policy" required>
      </div>
      <div class="full">
        <label style="display:flex; align-items:center; gap:8px; font-weight:400;">
          <input type="checkbox" name="absent_before_holiday_forfeits_pay" value="1" <?= $before ? 'checked' : '' ?> style="width:auto;">
          An unexcused absence the working day immediately before a holiday forfeits that holiday's pay
        </label>
      </div>
      <div class="full">
        <label style="display:flex; align-items:center; gap:8px; font-weight:400;">
          <input type="checkbox" name="absent_after_holiday_forfeits_pay" value="1" <?= $after ? 'checked' : '' ?> style="width:auto;">
          An unexcused absence the working day immediately after a holiday forfeits that holiday's pay
        </label>
      </div>
      <div>
        <label for="consecutive_absence_alert_days">Consecutive absence alert (days)</label>
        <input type="number" id="consecutive_absence_alert_days" name="consecutive_absence_alert_days" min="1" value="<?= $val('consecutive_absence_alert_days') ?>">
        <p class="muted" style="margin-top:6px;">Flags (doesn't block) an employee once their unexcused absences reach this many days in a row. Leave blank to disable.</p>
      </div>
      <div class="full">
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes" rows="2"><?= $val('notes') ?></textarea>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn primary"><?= $isEdit ? 'Save changes' : 'Add policy' ?></button>
      <a class="btn" href="<?= site_url('attendance-policies') ?>">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
