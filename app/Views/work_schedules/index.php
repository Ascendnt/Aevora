<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1>Work schedules</h1>
    <p class="sub"><a href="<?= site_url('attendance') ?>">&larr; Back to time &amp; attendance</a></p>
  </div>
  <a class="btn primary" href="<?= site_url('work-schedules/new' . ($filter ? '?company=' . $filter : '')) ?>"><i class="ti ti-plus" aria-hidden="true"></i>Add schedule</a>
</div>

<?php if (count($companies) > 1): ?>
  <form method="get" action="<?= site_url('work-schedules') ?>" style="margin-bottom:1rem; max-width:320px;">
    <label for="company">Filter by company</label>
    <select id="company" name="company" onchange="this.form.submit()">
      <option value="">All companies</option>
      <?php foreach ($companies as $c): ?>
        <option value="<?= esc($c['id']) ?>" <?= (int) $filter === (int) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </form>
<?php endif; ?>

<?php if (empty($schedules)): ?>
  <div class="empty">
    No work schedules yet. <a href="<?= site_url('work-schedules/new') ?>">Add one</a> to start tracking late/undertime.
  </div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Schedule</th>
          <?php if (count($companies) > 1): ?><th>Company</th><?php endif; ?>
          <th>Hours</th>
          <th>Grace</th>
          <th>Type</th>
          <th style="width:1%;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($schedules as $s): ?>
          <tr>
            <td><a href="<?= site_url('work-schedules/' . $s['id'] . '/edit') ?>"><strong><?= esc($s['name']) ?></strong></a></td>
            <?php if (count($companies) > 1): ?><td><?= esc($s['company_name']) ?></td><?php endif; ?>
            <td><?= esc(substr($s['time_in'], 0, 5)) ?> &ndash; <?= esc(substr($s['time_out'], 0, 5)) ?></td>
            <td><?= esc($s['grace_minutes']) ?> min</td>
            <td>
              <?= esc(ucfirst($s['schedule_type'])) ?>
              <?php if ($s['schedule_type'] === 'executive'): ?><div class="muted">Skips payroll late/undertime deductions</div><?php endif; ?>
            </td>
            <td style="white-space:nowrap;">
              <a class="btn sm" href="<?= site_url('work-schedules/' . $s['id'] . '/edit') ?>">Edit</a>
              <form method="post" action="<?= site_url('work-schedules/' . $s['id'] . '/delete') ?>" style="display:inline;"
                    onsubmit="return confirm('Delete schedule <?= esc($s['name'], 'js') ?>?');">
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
