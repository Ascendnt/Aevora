<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$isEdit    = $cutoff !== null;
$val       = static fn (string $key, $default = '') => esc(old($key, $cutoff[$key] ?? $default));
$selected  = (int) old('company_id', $cutoff['company_id'] ?? $preselect ?? 0);
$scopeType = old('scope_type', $cutoff['scope_type'] ?? 'company');
$scopeId   = (int) old('scope_id', $cutoff['scope_id'] ?? 0);
$frequency = old('frequency', $cutoff['frequency'] ?? 'semi_monthly');
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
        <select id="frequency" name="frequency" required>
          <option value="monthly" <?= $frequency === 'monthly' ? 'selected' : '' ?>>Monthly</option>
          <option value="semi_monthly" <?= $frequency === 'semi_monthly' ? 'selected' : '' ?>>Semi-monthly</option>
          <option value="weekly" <?= $frequency === 'weekly' ? 'selected' : '' ?>>Weekly</option>
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
