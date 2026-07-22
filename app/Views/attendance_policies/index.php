<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1>Attendance policies</h1>
    <p class="sub"><a href="<?= site_url('attendance') ?>">&larr; Back to time &amp; attendance</a></p>
  </div>
  <a class="btn primary" href="<?= site_url('attendance-policies/new') ?>"><i class="ti ti-plus" aria-hidden="true"></i>Add policy</a>
</div>

<?php if (empty($policies)): ?>
  <div class="empty">No attendance policies set up yet. Payroll treats holidays as fully paid regardless of surrounding attendance until you add one.</div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Company</th>
          <th>Name</th>
          <th>Absent before holiday</th>
          <th>Absent after holiday</th>
          <th>Consecutive absence alert</th>
          <th style="width:1%;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($policies as $p): ?>
          <tr>
            <td><?= esc($p['company_name']) ?></td>
            <td><a href="<?= site_url('attendance-policies/' . $p['id'] . '/edit') ?>"><?= esc($p['name']) ?></a></td>
            <td><span class="badge <?= db_bool($p['absent_before_holiday_forfeits_pay']) ? 'active' : 'inactive' ?>"><?= db_bool($p['absent_before_holiday_forfeits_pay']) ? 'Forfeits pay' : 'Still paid' ?></span></td>
            <td><span class="badge <?= db_bool($p['absent_after_holiday_forfeits_pay']) ? 'active' : 'inactive' ?>"><?= db_bool($p['absent_after_holiday_forfeits_pay']) ? 'Forfeits pay' : 'Still paid' ?></span></td>
            <td><?= $p['consecutive_absence_alert_days'] !== null ? esc($p['consecutive_absence_alert_days']) . ' day(s)' : '—' ?></td>
            <td style="white-space:nowrap;">
              <a class="btn sm" href="<?= site_url('attendance-policies/' . $p['id'] . '/edit') ?>">Edit</a>
              <form method="post" action="<?= site_url('attendance-policies/' . $p['id'] . '/delete') ?>" style="display:inline;"
                    onsubmit="return confirm('Delete policy <?= esc($p['name'], 'js') ?>?');">
                <?= csrf_field() ?>
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
