<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$isEdit = $loan !== null;
$val    = static fn (string $key, $default = '') => esc(old($key, $loan[$key] ?? $default));
$status = old('status', $loan['status'] ?? 'active');
?>

<div class="page-head">
  <div>
    <h1><?= $isEdit ? 'Edit loan' : 'Add loan' ?></h1>
    <p class="sub"><a href="<?= site_url('payroll/loans') ?>">&larr; Back to loans</a></p>
  </div>
</div>

<?php if ($isEdit && $deductionCount > 0): ?>
  <div class="alert error" style="margin-bottom:16px;">
    This loan has already had deductions applied in <?= esc($deductionCount) ?> finalized payslip(s). Editing it will not retroactively change those payslips. Check the confirmation box below to save changes.
  </div>
<?php endif; ?>

<div class="form-card">
  <form method="post" action="<?= $isEdit ? site_url('payroll/loans/' . $loan['id']) : site_url('payroll/loans') ?>">
    <?= csrf_field() ?>
    <div class="form-grid">
      <div class="full">
        <label for="employee_id">Employee *</label>
        <select id="employee_id" name="employee_id" required <?= $isEdit ? 'disabled' : '' ?>>
          <option value="">Choose an employee&hellip;</option>
          <?php foreach ($employees as $e): ?>
            <option value="<?= esc($e['id']) ?>" <?= (int) old('employee_id', $loan['employee_id'] ?? 0) === (int) $e['id'] ? 'selected' : '' ?>>
              <?= esc($e['user_name']) ?><?= $e['employee_number'] ? ' (' . esc($e['employee_number']) . ')' : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if ($isEdit): ?><input type="hidden" name="employee_id" value="<?= esc($loan['employee_id']) ?>"><?php endif; ?>
      </div>
      <div>
        <label for="loan_type">Loan type *</label>
        <input type="text" id="loan_type" name="loan_type" value="<?= $val('loan_type') ?>" placeholder="e.g. SSS salary loan, company loan" required>
      </div>
      <div>
        <label for="status">Status *</label>
        <select id="status" name="status" required>
          <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
          <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>Completed</option>
          <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>
      </div>
      <div>
        <label for="principal_amount">Principal amount *</label>
        <input type="number" id="principal_amount" name="principal_amount" step="0.01" min="0" value="<?= $val('principal_amount') ?>" required>
      </div>
      <div>
        <label for="monthly_amortization">Monthly amortization *</label>
        <input type="number" id="monthly_amortization" name="monthly_amortization" step="0.01" min="0" value="<?= $val('monthly_amortization') ?>" required>
      </div>
      <div>
        <label for="balance_remaining">Balance remaining<?= $isEdit ? ' *' : '' ?></label>
        <input type="number" id="balance_remaining" name="balance_remaining" step="0.01" min="0" value="<?= $val('balance_remaining') ?>" <?= $isEdit ? 'required' : '' ?> placeholder="<?= $isEdit ? '' : 'Defaults to principal amount' ?>">
      </div>
      <div>
        <label for="start_date">Start date *</label>
        <input type="date" id="start_date" name="start_date" value="<?= $val('start_date', date('Y-m-d')) ?>" required>
      </div>
      <div>
        <label for="end_date">End date</label>
        <input type="date" id="end_date" name="end_date" value="<?= $val('end_date') ?>">
      </div>
      <div class="full">
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes" rows="2"><?= $val('notes') ?></textarea>
      </div>
      <?php if ($isEdit && $deductionCount > 0): ?>
        <div class="full">
          <label style="display:flex; align-items:center; gap:8px; font-weight:400;">
            <input type="checkbox" name="confirm_override" value="1" required style="width:auto;">
            I understand this loan already had deductions applied in <?= esc($deductionCount) ?> payslip(s) and want to save anyway.
          </label>
        </div>
      <?php endif; ?>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn primary"><?= $isEdit ? 'Save changes' : 'Add loan' ?></button>
      <a class="btn" href="<?= site_url('payroll/loans') ?>">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
