<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$isEdit   = $schedule !== null;
$val      = static fn (string $key, $default = '') => esc(old($key, $schedule[$key] ?? $default));
$selected = (int) old('company_id', $schedule['company_id'] ?? $preselect ?? 0);
$type     = old('schedule_type', $schedule['schedule_type'] ?? 'fixed');
?>

<div class="page-head">
  <div>
    <h1><?= $isEdit ? 'Edit work schedule' : 'Add work schedule' ?></h1>
    <p class="sub"><a href="<?= site_url('work-schedules') ?>">&larr; Back to work schedules</a></p>
  </div>
</div>

<div class="form-card">
  <form method="post" action="<?= $isEdit ? site_url('work-schedules/' . $schedule['id']) : site_url('work-schedules') ?>">
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
        <label for="name">Schedule name *</label>
        <input type="text" id="name" name="name" value="<?= $val('name') ?>" placeholder="e.g. Day shift" required>
      </div>
      <div>
        <label for="time_in">Time in *</label>
        <input type="time" id="time_in" name="time_in" value="<?= $val('time_in') ?>" required>
      </div>
      <div>
        <label for="time_out">Time out *</label>
        <input type="time" id="time_out" name="time_out" value="<?= $val('time_out') ?>" required>
      </div>
      <div>
        <label for="grace_minutes">Grace period (minutes)</label>
        <input type="number" id="grace_minutes" name="grace_minutes" min="0" value="<?= $val('grace_minutes', 10) ?>">
      </div>
      <div>
        <label for="break_minutes">Break (minutes)</label>
        <input type="number" id="break_minutes" name="break_minutes" min="0" value="<?= $val('break_minutes') ?>">
      </div>
      <div class="full">
        <label for="schedule_type">Schedule type *</label>
        <select id="schedule_type" name="schedule_type" required>
          <option value="fixed" <?= $type === 'fixed' ? 'selected' : '' ?>>Fixed</option>
          <option value="shifting" <?= $type === 'shifting' ? 'selected' : '' ?>>Shifting</option>
          <option value="executive" <?= $type === 'executive' ? 'selected' : '' ?>>Executive</option>
        </select>
        <p class="muted" style="margin-top:6px;">
          <strong>Fixed</strong>: same hours every working day. <strong>Shifting</strong>: hours can be assigned per date (e.g. rotating shifts, including weekends).
          <strong>Executive</strong>: attendance is still logged, but late/undertime never generate payroll deductions.
        </p>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn primary"><?= $isEdit ? 'Save changes' : 'Add schedule' ?></button>
      <a class="btn" href="<?= site_url('work-schedules') ?>">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
