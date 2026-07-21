<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$freqLabel = static fn (string $f) => match ($f) {
    'monthly'      => 'Monthly',
    'weekly'       => 'Weekly',
    default        => 'Semi-monthly',
};
?>

<div class="page-head">
  <div>
    <h1>Cutoff schedules</h1>
    <p class="sub"><a href="<?= site_url('attendance') ?>">&larr; Back to time &amp; attendance</a></p>
  </div>
  <a class="btn primary" href="<?= site_url('cutoff-schedules/new' . ($filter ? '?company=' . $filter : '')) ?>"><i class="ti ti-plus" aria-hidden="true"></i>Add cutoff schedule</a>
</div>

<?php if (count($companies) > 1): ?>
  <form method="get" action="<?= site_url('cutoff-schedules') ?>" style="margin-bottom:1rem; max-width:320px;">
    <label for="company">Filter by company</label>
    <select id="company" name="company" onchange="this.form.submit()">
      <option value="">All companies</option>
      <?php foreach ($companies as $c): ?>
        <option value="<?= esc($c['id']) ?>" <?= (int) $filter === (int) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </form>
<?php endif; ?>

<?php if (! $filter): ?>
  <div class="empty">Choose a company above to see its cutoff schedules.</div>
<?php elseif (empty($schedules)): ?>
  <div class="empty">
    No cutoff schedules yet. <a href="<?= site_url('cutoff-schedules/new?company=' . $filter) ?>">Add one</a> to set pay period and reminder timing.
  </div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Scope</th>
          <th>Frequency</th>
          <th>Pay date offset</th>
          <th>Reminder</th>
          <th style="width:1%;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($schedules as $s): ?>
          <tr>
            <td><?= esc(ucfirst($s['scope_type'])) ?><div class="muted"><?= esc($s['scope_label']) ?></div></td>
            <td><?= esc($freqLabel($s['frequency'])) ?></td>
            <td><?= esc($s['pay_date_offset_days']) ?> day(s) after period end</td>
            <td><?= esc($s['reminder_days_before']) ?> day(s) before</td>
            <td style="white-space:nowrap;">
              <a class="btn sm" href="<?= site_url('cutoff-schedules/' . $s['id'] . '/edit') ?>">Edit</a>
              <form method="post" action="<?= site_url('cutoff-schedules/' . $s['id'] . '/delete') ?>" style="display:inline;"
                    onsubmit="return confirm('Delete this cutoff schedule?');">
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
