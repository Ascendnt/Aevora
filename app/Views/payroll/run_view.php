<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1>Payroll run</h1>
    <p class="sub">
      <a href="<?= site_url('payroll/runs') ?>">&larr; Back to payroll runs</a>
      &middot; <?= esc(date('M j', strtotime($run['period_start']))) ?> &ndash; <?= esc(date('M j, Y', strtotime($run['period_end']))) ?>
      &middot; <span class="badge <?= $isDraft ? 'inactive' : 'active' ?>"><?= esc(ucfirst($run['status'])) ?></span>
    </p>
  </div>
  <div style="display:flex; gap:10px; flex-wrap:wrap;">
    <a class="btn" href="<?= site_url('payroll/runs/' . $run['id'] . '/export') ?>"><i class="ti ti-file-spreadsheet" aria-hidden="true"></i>Export Excel</a>
    <?php if ($isDraft): ?>
      <form method="post" action="<?= site_url('payroll/runs/' . $run['id'] . '/finalize') ?>"
            onsubmit="return confirm('Finalize this run? Payslips will be locked as historical records and loan/benefit balances will be deducted. This cannot be undone from the UI.');">
        <?= csrf_field() ?>
        <button type="submit" class="btn primary">Finalize run</button>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php if ($isDraft): ?>
  <div class="form-card" style="max-width:none; margin-bottom:20px;">
    <p class="muted" style="margin:0;">
      This is a <strong>live draft</strong> &mdash; figures are recomputed from current attendance, filings, benefits and loans every time you view this page. Nothing is saved until you finalize.
    </p>
  </div>
  <div class="stat-grid">
    <div class="stat">
      <p class="label">Employees</p>
      <p class="value"><?= esc(count($rows)) ?></p>
    </div>
    <div class="stat">
      <p class="label">Gross total</p>
      <p class="value"><?= number_format($grossTotal, 2) ?></p>
    </div>
    <div class="stat">
      <p class="label">Net total</p>
      <p class="value"><?= number_format($netTotal, 2) ?></p>
    </div>
    <div class="stat">
      <p class="label">Errors</p>
      <p class="value"><?= esc(count(array_filter($rows, static fn ($r) => ! empty($r['error'])))) ?></p>
    </div>
  </div>
<?php endif; ?>

<?php if (empty($rows)): ?>
  <div class="empty">No employees to show for this run.</div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Employee</th>
          <th>Basic pay</th>
          <th>Days worked</th>
          <th>Late / Undertime (min)</th>
          <th>Deductions</th>
          <th>Gross</th>
          <th>Tax</th>
          <th>Benefits</th>
          <th>Loan ded.</th>
          <th>Net pay</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
          <?php if (! empty($row['error'])): ?>
            <tr>
              <td><strong><?= esc($row['employee_name'] ?? 'Employee #' . ($row['employee_id'] ?? '?')) ?></strong></td>
              <td colspan="9" style="color:var(--danger, #d33);"><?= esc($row['error']) ?></td>
            </tr>
            <?php continue; ?>
          <?php endif; ?>
          <tr>
            <td>
              <strong><?= esc($row['employee_name'] ?? '') ?></strong>
              <?php if (! empty($row['employee_number'])): ?><div class="muted"><?= esc($row['employee_number']) ?></div><?php endif; ?>
            </td>
            <td><?= number_format((float) $row['basic_pay'], 2) ?></td>
            <td><?= esc($row['days_worked']) ?></td>
            <td><?= esc($row['late_minutes']) ?> / <?= esc($row['undertime_minutes']) ?></td>
            <td><?= number_format(((float) $row['late_deduction']) + ((float) $row['undertime_deduction']) + ((float) $row['absence_deduction']), 2) ?></td>
            <td><strong><?= number_format((float) $row['gross_pay'], 2) ?></strong></td>
            <td><?= number_format((float) $row['tax_withheld'], 2) ?></td>
            <td><?= number_format((float) $row['benefits_total'], 2) ?></td>
            <td><?= number_format((float) $row['loan_deductions_total'], 2) ?></td>
            <td><strong><?= number_format((float) $row['net_pay'], 2) ?></strong></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?= $this->endSection() ?>
