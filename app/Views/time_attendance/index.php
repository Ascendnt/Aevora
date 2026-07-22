<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

$statusBadge = static function (?string $status): string {
    return match ($status) {
        'on_time'   => '<span class="badge active">On time</span>',
        'absent'    => '<span class="badge" style="background:var(--bg-danger); color:var(--text-danger);">Absent</span>',
        'late'      => '<span class="badge" style="background:var(--bg-accent); color:var(--text-accent);">Late</span>',
        'undertime' => '<span class="badge" style="background:var(--bg-accent); color:var(--text-accent);">Undertime</span>',
        default     => '<span class="badge inactive">&mdash;</span>',
    };
};
?>

<div class="page-head">
  <div>
    <h1>Time &amp; attendance</h1>
    <p class="sub">Clock in/out, and review your recent attendance</p>
  </div>
  <div style="display:flex; gap:8px; flex-wrap:wrap;">
    <?php if (can_access_sub('time_attendance.schedules', \App\Constants\Modules::TIME_ATTENDANCE)): ?>
      <a class="btn" href="<?= site_url('work-schedules') ?>"><i class="ti ti-calendar-time" aria-hidden="true"></i>Work schedules</a>
    <?php endif; ?>
    <?php if (can_access_sub('time_attendance.holidays', \App\Constants\Modules::TIME_ATTENDANCE)): ?>
      <a class="btn" href="<?= site_url('holidays') ?>"><i class="ti ti-calendar-star" aria-hidden="true"></i>Holidays</a>
    <?php endif; ?>
    <?php if (can_access_sub('time_attendance.cutoff', \App\Constants\Modules::TIME_ATTENDANCE)): ?>
      <a class="btn" href="<?= site_url('cutoff-schedules') ?>"><i class="ti ti-calendar-cog" aria-hidden="true"></i>Cutoff schedules</a>
    <?php endif; ?>
    <?php if (can_access_sub('time_attendance.policies', \App\Constants\Modules::TIME_ATTENDANCE)): ?>
      <a class="btn" href="<?= site_url('attendance-policies') ?>"><i class="ti ti-shield-check" aria-hidden="true"></i>Policies</a>
    <?php endif; ?>
  </div>
</div>

<?php if ($noEmployee): ?>
  <div class="empty">This account has no employee profile, so there is no personal attendance to show.</div>
<?php else: ?>

  <div class="form-card" style="margin-bottom:20px; max-width:none;">
    <p class="section-label">Today &mdash; <?= esc(date('l, F j, Y')) ?></p>
    <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
      <form method="post" action="<?= site_url('attendance/clock-in') ?>">
        <?= csrf_field() ?>
        <button type="submit" class="btn primary" <?= ! empty($todayLog['time_in']) ? 'disabled' : '' ?>>
          <i class="ti ti-login-2" aria-hidden="true"></i>
          <?= ! empty($todayLog['time_in']) ? 'Clocked in at ' . esc(substr($todayLog['time_in'], 0, 5)) : 'Clock in' ?>
        </button>
      </form>
      <form method="post" action="<?= site_url('attendance/clock-out') ?>">
        <?= csrf_field() ?>
        <button type="submit" class="btn" <?= ! empty($todayLog['time_out']) ? 'disabled' : '' ?>>
          <i class="ti ti-logout-2" aria-hidden="true"></i>
          <?= ! empty($todayLog['time_out']) ? 'Clocked out at ' . esc(substr($todayLog['time_out'], 0, 5)) : 'Clock out' ?>
        </button>
      </form>
    </div>
    <?php if (! empty($todayLog['timezone'])): ?>
      <p class="muted" style="margin:8px 0 0;">Detected timezone: <?= esc($todayLog['timezone']) ?> (from your connection). <a href="<?= site_url('filings/new?filing_type=time_adjustment') ?>">This wrong?</a></p>
    <?php endif; ?>
  </div>

  <p class="section-label"><?= esc($periodLabel) ?></p>

  <?php if (empty($rows)): ?>
    <div class="empty">No attendance to show yet this period.</div>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Date</th>
            <th>Schedule</th>
            <th>Time in</th>
            <th>Time out</th>
            <th>Status</th>
            <th style="width:1%;"></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
            <tr>
              <td>
                <strong><?= esc(date('M j, Y', strtotime($row['date']))) ?></strong>
                <div class="muted"><?= esc($dayNames[$row['dow'] - 1]) ?><?= $row['is_holiday'] ? ' · ' . esc($row['holiday_name']) : '' ?></div>
              </td>
              <td><?= esc($row['schedule']['name'] ?? 'No schedule assigned') ?></td>
              <td><?= esc(! empty($row['log']['time_in']) ? substr($row['log']['time_in'], 0, 5) : '—') ?></td>
              <td><?= esc(! empty($row['log']['time_out']) ? substr($row['log']['time_out'], 0, 5) : '—') ?></td>
              <td>
                <?= $statusBadge($row['status']) ?>
                <?php if ($row['late_minutes'] > 0): ?><div class="muted"><?= esc($row['late_minutes']) ?> min late</div><?php endif; ?>
                <?php if ($row['undertime_minutes'] > 0): ?><div class="muted"><?= esc($row['undertime_minutes']) ?> min short</div><?php endif; ?>
              </td>
              <td style="white-space:nowrap;">
                <?php if (in_array($row['status'], ['late', 'undertime', 'absent'], true)): ?>
                  <a class="btn sm" href="<?= site_url('filings/new?type=time_adjustment&date=' . $row['date']) ?>">File correction</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

<?php endif; ?>

<?= $this->endSection() ?>
