<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$isEdit    = $benefit !== null;
$val       = static fn (string $key, $default = '') => esc(old($key, $benefit[$key] ?? $default));
$recurring = old('is_recurring', $isEdit ? (db_bool($benefit['is_recurring']) ? '1' : '') : '1');
?>

<div class="page-head">
  <div>
    <h1><?= $isEdit ? 'Edit benefit' : 'Add benefit' ?></h1>
    <p class="sub"><a href="<?= site_url('payroll/benefits') ?>">&larr; Back to benefits</a></p>
  </div>
</div>

<?php if ($isEdit && $appliedCount > 0): ?>
  <div class="alert error" style="margin-bottom:16px;">
    This benefit has already been applied in <?= esc($appliedCount) ?> finalized payslip(s). Editing it will not retroactively change those payslips, but will affect future annualization/reporting. Check the confirmation box below to save changes.
  </div>
<?php endif; ?>

<div class="form-card">
  <form method="post" action="<?= $isEdit ? site_url('payroll/benefits/' . $benefit['id']) : site_url('payroll/benefits') ?>">
    <?= csrf_field() ?>
    <div class="form-grid">
      <div class="full">
        <label for="employee_id">Employee *</label>
        <select id="employee_id" name="employee_id" required <?= $isEdit ? 'disabled' : '' ?>>
          <option value="">Choose an employee&hellip;</option>
          <?php foreach ($employees as $e): ?>
            <option value="<?= esc($e['id']) ?>" <?= (int) old('employee_id', $benefit['employee_id'] ?? 0) === (int) $e['id'] ? 'selected' : '' ?>>
              <?= esc($e['user_name']) ?><?= $e['employee_number'] ? ' (' . esc($e['employee_number']) . ')' : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if ($isEdit): ?><input type="hidden" name="employee_id" value="<?= esc($benefit['employee_id']) ?>"><?php endif; ?>
      </div>
      <div>
        <label for="benefit_type">Benefit type *</label>
        <input type="text" id="benefit_type" name="benefit_type" value="<?= $val('benefit_type') ?>" placeholder="e.g. Rice allowance, HMO, 13th month" required>
      </div>
      <div>
        <label for="amount">Amount per period *</label>
        <input type="number" id="amount" name="amount" step="0.01" min="0" value="<?= $val('amount') ?>" required>
      </div>
      <div>
        <label for="effective_date">Effective date *</label>
        <input type="date" id="effective_date" name="effective_date" value="<?= $val('effective_date', date('Y-m-d')) ?>" required>
      </div>
      <div>
        <label for="end_date">End date</label>
        <input type="date" id="end_date" name="end_date" value="<?= $val('end_date') ?>">
      </div>
      <div class="full">
        <label style="display:flex; align-items:center; gap:8px; font-weight:400;">
          <input type="checkbox" name="is_recurring" value="1" <?= $recurring ? 'checked' : '' ?> style="width:auto;">
          Recurring every payroll period until ended
        </label>
      </div>
      <div class="full">
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes" rows="2"><?= $val('notes') ?></textarea>
      </div>
      <?php if ($isEdit && $appliedCount > 0): ?>
        <div class="full">
          <label style="display:flex; align-items:center; gap:8px; font-weight:400;">
            <input type="checkbox" name="confirm_override" value="1" required style="width:auto;">
            I understand this benefit was already used in <?= esc($appliedCount) ?> payslip(s) and want to save anyway.
          </label>
        </div>
      <?php endif; ?>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn primary"><?= $isEdit ? 'Save changes' : 'Add benefit' ?></button>
      <a class="btn" href="<?= site_url('payroll/benefits') ?>">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
