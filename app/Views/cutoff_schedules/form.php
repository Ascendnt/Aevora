<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$isEdit    = $cutoff !== null;
$val       = static fn (string $key, $default = '') => esc(old($key, $cutoff[$key] ?? $default));
$selected  = (int) old('company_id', $cutoff['company_id'] ?? $preselect ?? 0);
$scopeType = old('scope_type', $cutoff['scope_type'] ?? 'company');
$scopeId   = (int) old('scope_id', $cutoff['scope_id'] ?? 0);
$frequency = old('frequency', $cutoff['frequency'] ?? 'semi_monthly');

$periodConfig = [];
if ($cutoff !== null && ! empty($cutoff['period_config'])) {
    $decoded      = json_decode((string) $cutoff['period_config'], true);
    $periodConfig = is_array($decoded) ? $decoded : [];
}
$cutoffDay   = old('cutoff_day', (string) ($periodConfig['day_of_month'] ?? $periodConfig['cutoff_day'] ?? ''));
$cutoffDays  = $periodConfig['cutoff_days'] ?? $periodConfig['days'] ?? [];
$cutoffDay1  = old('cutoff_day_1', (string) ($cutoffDays[0] ?? ''));
$cutoffDay2  = old('cutoff_day_2', (string) ($cutoffDays[1] ?? ''));
$weekdayVal  = old('cutoff_weekday', (string) ($periodConfig['weekday'] ?? $periodConfig['day_of_week'] ?? '5'));
$weekdays    = ['0' => 'Sunday', '1' => 'Monday', '2' => 'Tuesday', '3' => 'Wednesday', '4' => 'Thursday', '5' => 'Friday', '6' => 'Saturday'];
?>

<div class="page-head">
  <div>
    <h1><?= $isEdit ? 'Edit cutoff schedule' : 'Add cutoff schedule' ?></h1>
    <p class="sub"><a href="<?= site_url('cutoff-schedules') ?>">&larr; Back to cutoff schedules</a></p>
  </div>
</div>

<div class="form-card">
  <form method="post" action="<?= $isEdit ? site_url('cutoff-schedules/' . $cutoff['id']) : site_url('cutoff-schedules') ?>">
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
          <p class="muted" style="margin-top:6px;">Switch company and save once to reload the department/branch/employee choices below for that company.</p>
        <?php endif; ?>
      </div>
      <div>
        <label for="frequency">Frequency *</label>
        <select id="frequency" name="frequency" required onchange="cutoffFrequencyChanged(this.value)">
          <option value="monthly" <?= $frequency === 'monthly' ? 'selected' : '' ?>>Monthly</option>
          <option value="semi_monthly" <?= $frequency === 'semi_monthly' ? 'selected' : '' ?>>Semi-monthly</option>
          <option value="weekly" <?= $frequency === 'weekly' ? 'selected' : '' ?>>Weekly</option>
        </select>
      </div>

      <div id="freqMonthlyRow" style="display:none;">
        <label for="cutoff_day">Cutoff day of month</label>
        <input type="text" id="cutoff_day" name="cutoff_day" value="<?= esc($cutoffDay) ?>" placeholder="e.g. 31 or last">
        <p class="muted" style="margin-top:6px;">1–31, or "last" for the last day of the month.</p>
      </div>

      <div id="freqSemiMonthlyRow" class="full" style="display:none;">
        <label>Cutoff days</label>
        <div style="display:flex; gap:14px; max-width:400px;">
          <input type="text" name="cutoff_day_1" value="<?= esc($cutoffDay1) ?>" placeholder="e.g. 15">
          <input type="text" name="cutoff_day_2" value="<?= esc($cutoffDay2) ?>" placeholder="e.g. 30 or last">
        </div>
        <p class="muted" style="margin-top:6px;">Two cutoff days per month — e.g. 15 and 30, or 5 and 20. Use "last" for the last day of the month.</p>
      </div>

      <div id="freqWeeklyRow" style="display:none;">
        <label for="cutoff_weekday">Cutoff weekday</label>
        <select id="cutoff_weekday" name="cutoff_weekday">
          <?php foreach ($weekdays as $val => $label): ?>
            <option value="<?= esc($val) ?>" <?= $weekdayVal === $val ? 'selected' : '' ?>><?= esc($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="full">
        <label for="scope_type">Applies to *</label>
        <select id="scope_type" name="scope_type" required onchange="cutoffScopeChanged(this.value)">
          <option value="company" <?= $scopeType === 'company' ? 'selected' : '' ?>>Whole company</option>
          <option value="department" <?= $scopeType === 'department' ? 'selected' : '' ?>>A specific department</option>
          <option value="branch" <?= $scopeType === 'branch' ? 'selected' : '' ?>>A specific branch</option>
          <option value="employee" <?= $scopeType === 'employee' ? 'selected' : '' ?>>A specific employee</option>
        </select>
      </div>

      <div class="full" id="scopeDepartmentRow" style="display:none;">
        <label for="scope_id_department">Department</label>
        <select id="scope_id_department" name="scope_id_department">
          <option value="">Choose a department&hellip;</option>
          <?php foreach ($departments as $d): ?>
            <option value="<?= esc($d['id']) ?>" <?= $scopeType === 'department' && $scopeId === (int) $d['id'] ? 'selected' : '' ?>><?= esc($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="full" id="scopeBranchRow" style="display:none;">
        <label for="scope_id_branch">Branch</label>
        <select id="scope_id_branch" name="scope_id_branch">
          <option value="">Choose a branch&hellip;</option>
          <?php foreach ($branches as $b): ?>
            <option value="<?= esc($b['id']) ?>" <?= $scopeType === 'branch' && $scopeId === (int) $b['id'] ? 'selected' : '' ?>><?= esc($b['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="full" id="scopeEmployeeRow" style="display:none;">
        <label for="scope_id_employee">Employee</label>
        <select id="scope_id_employee" name="scope_id_employee">
          <option value="">Choose an employee&hellip;</option>
          <?php foreach ($employees as $e): ?>
            <option value="<?= esc($e['id']) ?>" <?= $scopeType === 'employee' && $scopeId === (int) $e['id'] ? 'selected' : '' ?>><?= esc($e['user_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label for="pay_date_offset_days">Pay date offset (days after period end)</label>
        <input type="number" id="pay_date_offset_days" name="pay_date_offset_days" min="0" value="<?= $val('pay_date_offset_days', 5) ?>">
      </div>
      <div>
        <label for="reminder_days_before">Reminder (days before period end)</label>
        <input type="number" id="reminder_days_before" name="reminder_days_before" min="0" value="<?= $val('reminder_days_before', 2) ?>">
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn primary"><?= $isEdit ? 'Save changes' : 'Add cutoff schedule' ?></button>
      <a class="btn" href="<?= site_url('cutoff-schedules') ?>">Cancel</a>
    </div>
  </form>
</div>

<script>
  function cutoffFrequencyChanged(value) {
    document.getElementById('freqMonthlyRow').style.display = value === 'monthly' ? 'block' : 'none';
    document.getElementById('freqSemiMonthlyRow').style.display = value === 'semi_monthly' ? 'block' : 'none';
    document.getElementById('freqWeeklyRow').style.display = value === 'weekly' ? 'block' : 'none';
  }
  document.addEventListener('DOMContentLoaded', function () {
    cutoffFrequencyChanged(document.getElementById('frequency').value);
  });

  function cutoffScopeChanged(value) {
    document.getElementById('scopeDepartmentRow').style.display = value === 'department' ? 'block' : 'none';
    document.getElementById('scopeBranchRow').style.display = value === 'branch' ? 'block' : 'none';
    document.getElementById('scopeEmployeeRow').style.display = value === 'employee' ? 'block' : 'none';
  }
  document.addEventListener('DOMContentLoaded', function () {
    var scopeType = document.getElementById('scope_type');
    cutoffScopeChanged(scopeType.value);

    // Copy whichever dependent select is active into a single hidden "scope_id"
    // field the controller reads, since only one of the three is relevant at a time.
    var form = scopeType.closest('form');
    var hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.name = 'scope_id';
    form.appendChild(hidden);

    form.addEventListener('submit', function () {
      var map = { department: 'scope_id_department', branch: 'scope_id_branch', employee: 'scope_id_employee' };
      var fieldId = map[scopeType.value];
      hidden.value = fieldId ? document.getElementById(fieldId).value : '';
    });
  });
</script>

<?= $this->endSection() ?>
