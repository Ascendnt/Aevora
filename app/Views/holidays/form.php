<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$isEdit    = $holiday !== null;
$val       = static fn (string $key, $default = '') => esc(old($key, $holiday[$key] ?? $default));
$selected  = (int) old('company_id', $holiday['company_id'] ?? $preselect ?? 0);
$type      = old('holiday_type', $holiday['holiday_type'] ?? 'legal');
$scopeType = old('scope_type', $holiday['scope_type'] ?? 'national');
?>

<div class="page-head">
  <div>
    <h1><?= $isEdit ? 'Edit holiday' : 'Add holiday' ?></h1>
    <p class="sub"><a href="<?= site_url('holidays') ?>">&larr; Back to holidays</a></p>
  </div>
</div>

<div class="form-card">
  <form method="post" action="<?= $isEdit ? site_url('holidays/' . $holiday['id']) : site_url('holidays') ?>">
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
        <label for="name">Holiday name *</label>
        <input type="text" id="name" name="name" value="<?= $val('name') ?>" required>
      </div>
      <div>
        <label for="date">Date *</label>
        <input type="date" id="date" name="date" value="<?= $val('date') ?>" required>
      </div>
      <div>
        <label for="holiday_type">Holiday type *</label>
        <select id="holiday_type" name="holiday_type" required>
          <option value="legal" <?= $type === 'legal' ? 'selected' : '' ?>>Legal</option>
          <option value="special" <?= $type === 'special' ? 'selected' : '' ?>>Special</option>
        </select>
      </div>
      <div>
        <label for="scope_type">Scope *</label>
        <select id="scope_type" name="scope_type" required onchange="holidayScopeChanged(this.value)">
          <option value="national" <?= $scopeType === 'national' ? 'selected' : '' ?>>National</option>
          <option value="regional" <?= $scopeType === 'regional' ? 'selected' : '' ?>>Regional</option>
          <option value="local" <?= $scopeType === 'local' ? 'selected' : '' ?>>Local</option>
          <option value="branch" <?= $scopeType === 'branch' ? 'selected' : '' ?>>A specific branch</option>
          <option value="employee" <?= $scopeType === 'employee' ? 'selected' : '' ?>>A specific employee</option>
        </select>
        <p class="muted" style="margin-top:6px;">National/regional/local are for government-mandated holidays. Branch/employee are for a holiday only that branch or person observes.</p>
      </div>
      <div id="scopeValueRow" class="full" style="display:none;">
        <label for="scope_value">Region / locality</label>
        <input type="text" id="scope_value" name="scope_value" value="<?= $val('scope_value') ?>" placeholder="e.g. Metro Manila">
      </div>
      <div id="scopeBranchRow" class="full" style="display:none;">
        <label for="branch_id">Branch</label>
        <select id="branch_id" name="branch_id">
          <option value="">Choose a branch&hellip;</option>
          <?php foreach ($branches as $b): ?>
            <option value="<?= esc($b['id']) ?>" <?= (int) old('branch_id', $holiday['branch_id'] ?? 0) === (int) $b['id'] ? 'selected' : '' ?>><?= esc($b['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div id="scopeEmployeeRow" class="full" style="display:none;">
        <label for="employee_id">Employee</label>
        <select id="employee_id" name="employee_id">
          <option value="">Choose an employee&hellip;</option>
          <?php foreach ($employees as $e): ?>
            <option value="<?= esc($e['id']) ?>" <?= (int) old('employee_id', $holiday['employee_id'] ?? 0) === (int) $e['id'] ? 'selected' : '' ?>><?= esc($e['user_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn primary"><?= $isEdit ? 'Save changes' : 'Add holiday' ?></button>
      <a class="btn" href="<?= site_url('holidays') ?>">Cancel</a>
    </div>
  </form>
</div>

<script>
  function holidayScopeChanged(value) {
    document.getElementById('scopeValueRow').style.display = (value === 'regional' || value === 'local') ? 'block' : 'none';
    document.getElementById('scopeBranchRow').style.display = value === 'branch' ? 'block' : 'none';
    document.getElementById('scopeEmployeeRow').style.display = value === 'employee' ? 'block' : 'none';
  }
  document.addEventListener('DOMContentLoaded', function () {
    holidayScopeChanged(document.getElementById('scope_type').value);
  });
</script>

<?= $this->endSection() ?>
