<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1>Employee loans</h1>
    <p class="sub"><a href="<?= site_url('payroll') ?>">&larr; Back to payroll dashboard</a></p>
  </div>
  <div style="display:flex; gap:10px;">
    <a class="btn" href="<?= site_url('payroll/import') ?>">Bulk import</a>
    <a class="btn primary" href="<?= site_url('payroll/loans/new') ?>"><i class="ti ti-plus" aria-hidden="true"></i>Add loan</a>
  </div>
</div>

<?php if (empty($loans)): ?>
  <div class="empty">No loans added yet.</div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Employee</th>
          <?php if (scoped_company_id() === null): ?><th>Company</th><?php endif; ?>
          <th>Type</th>
          <th>Principal</th>
          <th>Monthly amort.</th>
          <th>Balance</th>
          <th>Status</th>
          <th style="width:1%;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($loans as $l): ?>
          <tr>
            <td><?= esc($l['employee_name']) ?><?php if ((int) $l['deduction_count'] > 0): ?><div class="muted">Deducted in <?= esc($l['deduction_count']) ?> payslip(s)</div><?php endif; ?></td>
            <?php if (scoped_company_id() === null): ?><td><?= esc($l['company_name']) ?></td><?php endif; ?>
            <td><?= esc($l['loan_type']) ?></td>
            <td><?= number_format((float) $l['principal_amount'], 2) ?></td>
            <td><?= number_format((float) $l['monthly_amortization'], 2) ?></td>
            <td><?= number_format((float) $l['balance_remaining'], 2) ?></td>
            <td><span class="badge <?= $l['status'] === 'active' ? 'active' : 'inactive' ?>"><?= esc(ucfirst($l['status'])) ?></span></td>
            <td style="white-space:nowrap;">
              <a class="btn sm" href="<?= site_url('payroll/loans/' . $l['id'] . '/edit') ?>">Edit</a>
              <form method="post" action="<?= site_url('payroll/loans/' . $l['id'] . '/delete') ?>" style="display:inline;"
                    onsubmit="return confirm('<?= (int) $l['deduction_count'] > 0 ? 'This loan has already had deductions applied in ' . (int) $l['deduction_count'] . ' payslip(s). ' : '' ?>Delete this loan?');">
                <?= csrf_field() ?>
                <?php if ((int) $l['deduction_count'] > 0): ?><input type="hidden" name="confirm_override" value="1"><?php endif; ?>
                <button type="submit" class="btn sm danger">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?= $this->endSection() ?>
