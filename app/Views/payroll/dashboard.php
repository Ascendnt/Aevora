<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1>Payroll</h1>
    <p class="sub">Live, attendance-driven payroll for the current cutoff</p>
  </div>
  <div style="display:flex; gap:10px; flex-wrap:wrap;">
    <a class="btn" href="<?= site_url('payroll/benefits') ?>">Benefits</a>
    <a class="btn" href="<?= site_url('payroll/loans') ?>">Loans</a>
    <a class="btn" href="<?= site_url('payroll/import') ?>">Bulk import</a>
    <a class="btn" href="<?= site_url('payroll/runs') ?>">All runs</a>
    <a class="btn primary" href="<?= site_url('payroll/runs/new') ?>"><i class="ti ti-plus" aria-hidden="true"></i>New run</a>
  </div>
</div>

<div class="stat-grid">
  <div class="stat">
    <p class="label">Active employees</p>
    <p class="value"><?= esc($headcount) ?></p>
  </div>
  <div class="stat">
    <p class="label">Draft runs</p>
    <p class="value"><?= esc($draftCount) ?></p>
  </div>
  <div class="stat">
    <p class="label">Draft gross (current)</p>
    <p class="value"><?= $draftSummary ? number_format($draftSummary['gross_total'], 2) : '—' ?></p>
  </div>
  <div class="stat">
    <p class="label">Draft net (current)</p>
    <p class="value"><?= $draftSummary ? number_format($draftSummary['net_total'], 2) : '—' ?></p>
  </div>
</div>

<?php if ($draftRun): ?>
  <p class="section-label">Current draft run</p>
  <div class="form-card" style="max-width:none;">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
      <div>
        <p style="margin:0 0 4px; font-weight:600;">
          <?= esc(date('M j, Y', strtotime($draftRun['period_start']))) ?> &ndash; <?= esc(date('M j, Y', strtotime($draftRun['period_end']))) ?>
        </p>
        <p class="muted" style="margin:0;">
          <?php if ($draftSummary): ?>
            <?= esc($draftSummary['employee_count']) ?> employee(s) computed
            <?php if ($draftSummary['error_count'] > 0): ?>
              &middot; <span style="color:var(--danger, #d33);"><?= esc($draftSummary['error_count']) ?> with errors (missing basic pay, etc.)</span>
            <?php endif; ?>
          <?php else: ?>
            No active employees to compute yet.
          <?php endif; ?>
        </p>
      </div>
      <a class="btn primary" href="<?= site_url('payroll/runs/' . $draftRun['id']) ?>">Review &amp; finalize</a>
    </div>
  </div>
<?php else: ?>
  <div class="empty">No draft payroll run yet. <a href="<?= site_url('payroll/runs/new') ?>">Create one</a> for the current cutoff to see live, auto-computed totals build up as attendance comes in.</div>
<?php endif; ?>

<?php if (! empty($reminders)): ?>
  <p class="section-label">Cutoff reminders</p>
  <div class="table-wrap" style="margin-bottom:20px;">
    <table>
      <thead>
        <tr>
          <?php if (scoped_company_id() === null): ?><th>Company</th><?php endif; ?>
          <th>Frequency</th>
          <th>Period ends</th>
          <th>Due</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($reminders as $r): ?>
          <tr>
            <?php if (scoped_company_id() === null): ?><td><?= esc($r['company_name'] ?? '—') ?></td><?php endif; ?>
            <td><?= esc(ucfirst(str_replace('_', ' ', $r['schedule']['frequency'] ?? ''))) ?></td>
            <td><?= esc(date('M j, Y', strtotime($r['period_end']))) ?></td>
            <td><?= $r['days_until'] <= 0 ? 'Today' : ($r['days_until'] === 1 ? 'In 1 day' : 'In ' . esc($r['days_until']) . ' days') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<p class="section-label">Recent runs</p>
<?php if (empty($recentRuns)): ?>
  <div class="empty">No payroll runs yet.</div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <?php if (scoped_company_id() === null): ?><th>Company</th><?php endif; ?>
          <th>Period</th>
          <th>Pay date</th>
          <th>Status</th>
          <th style="width:1%;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentRuns as $run): ?>
          <tr>
            <?php if (scoped_company_id() === null): ?><td><?= esc($run['company_name']) ?></td><?php endif; ?>
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
