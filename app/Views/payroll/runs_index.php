<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1>Payroll runs</h1>
    <p class="sub"><a href="<?= site_url('payroll') ?>">&larr; Back to payroll dashboard</a></p>
  </div>
  <a class="btn primary" href="<?= site_url('payroll/runs/new') ?>"><i class="ti ti-plus" aria-hidden="true"></i>New run</a>
</div>

<?php if (empty($runs)): ?>
  <div class="empty">No payroll runs yet. <a href="<?= site_url('payroll/runs/new') ?>">Create the first one</a>.</div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Company</th>
          <th>Period</th>
          <th>Pay date</th>
          <th>Status</th>
          <th style="width:1%;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($runs as $run): ?>
          <tr>
            <td><?= esc($run['company_name']) ?></td>
            <td><?= esc(date('M j', strtotime($run['period_start']))) ?> &ndash; <?= esc(date('M j, Y', strtotime($run['period_end']))) ?></td>
            <td><?= $run['pay_date'] ? esc(date('M j, Y', strtotime($run['pay_date']))) : '—' ?></td>
            <td><span class="badge <?= $run['status'] === 'draft' ? 'inactive' : 'active' ?>"><?= esc(ucfirst($run['status'])) ?></span></td>
            <td style="white-space:nowrap;"><a class="btn sm" href="<?= site_url('payroll/runs/' . $run['id']) ?>">View</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?= $this->endSection() ?>
