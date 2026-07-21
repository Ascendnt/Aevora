<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1>Employee benefits</h1>
    <p class="sub"><a href="<?= site_url('payroll') ?>">&larr; Back to payroll dashboard</a></p>
  </div>
  <div style="display:flex; gap:10px;">
    <a class="btn" href="<?= site_url('payroll/import') ?>">Bulk import</a>
    <a class="btn primary" href="<?= site_url('payroll/benefits/new') ?>"><i class="ti ti-plus" aria-hidden="true"></i>Add benefit</a>
  </div>
</div>

<?php if (empty($benefits)): ?>
  <div class="empty">No benefits added yet.</div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Employee</th>
          <?php if (scoped_company_id() === null): ?><th>Company</th><?php endif; ?>
          <th>Type</th>
          <th>Amount</th>
          <th>Recurring</th>
          <th>Effective</th>
          <th>End</th>
          <th style="width:1%;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($benefits as $b): ?>
          <tr>
            <td><?= esc($b['employee_name']) ?><?php if ((int) $b['applied_count'] > 0): ?><div class="muted">Applied in <?= esc($b['applied_count']) ?> payslip(s)</div><?php endif; ?></td>
            <?php if (scoped_company_id() === null): ?><td><?= esc($b['company_name']) ?></td><?php endif; ?>
            <td><?= esc($b['benefit_type']) ?></td>
            <td><?= number_format((float) $b['amount'], 2) ?></td>
            <td><span class="badge <?= db_bool($b['is_recurring']) ? 'active' : 'inactive' ?>"><?= db_bool($b['is_recurring']) ? 'Recurring' : 'One-off' ?></span></td>
            <td><?= esc(date('M j, Y', strtotime($b['effective_date']))) ?></td>
            <td><?= $b['end_date'] ? esc(date('M j, Y', strtotime($b['end_date']))) : '—' ?></td>
            <td style="white-space:nowrap;">
              <a class="btn sm" href="<?= site_url('payroll/benefits/' . $b['id'] . '/edit') ?>">Edit</a>
              <form method="post" action="<?= site_url('payroll/benefits/' . $b['id'] . '/delete') ?>" style="display:inline;"
                    onsubmit="return confirm('<?= (int) $b['applied_count'] > 0 ? 'This benefit has already been applied to ' . (int) $b['applied_count'] . ' payslip(s). ' : '' ?>Delete this benefit?');">
                <?= csrf_field() ?>
                <?php if ((int) $b['applied_count'] > 0): ?><input type="hidden" name="confirm_override" value="1"><?php endif; ?>
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
