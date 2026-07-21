<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$typeLabels = [
    'leave'             => 'Leave',
    'official_business' => 'Official business',
    'schedule_change'   => 'Schedule change',
    'time_adjustment'   => 'Time adjustment',
];

$statusBadge = static function (string $status): string {
    return match ($status) {
        'approved'  => '<span class="badge active">Approved</span>',
        'rejected'  => '<span class="badge" style="background:var(--bg-danger); color:var(--text-danger);">Rejected</span>',
        'cancelled' => '<span class="badge inactive">Cancelled</span>',
        default     => '<span class="badge inactive">Pending</span>',
    };
};

$fmtDates = static function (array $dates): string {
    if ($dates === []) {
        return '—';
    }
    if (count($dates) === 1) {
        return esc(date('M j, Y', strtotime($dates[0])));
    }

    return esc(date('M j', strtotime($dates[0])) . ' – ' . date('M j, Y', strtotime(end($dates))))
        . ' <span class="muted">(' . count($dates) . ' day' . (count($dates) === 1 ? '' : 's') . ')</span>';
};
?>

<div class="page-head">
  <div>
    <h1>My filings</h1>
    <p class="sub">Leave, official business, schedule changes, and time adjustments you've filed</p>
  </div>
  <div style="display:flex; gap:10px;">
    <a class="btn" href="<?= site_url('filings/my-approvals') ?>"><i class="ti ti-checklist" aria-hidden="true"></i>My approvals</a>
    <a class="btn primary" href="<?= site_url('filings/new') ?>"><i class="ti ti-plus" aria-hidden="true"></i>New filing</a>
  </div>
</div>

<?php if (! $employee): ?>
  <div class="empty">Only employees can file — superadmin accounts have no filings of their own.</div>
<?php elseif (empty($filings)): ?>
  <div class="empty">
    No filings yet. <a href="<?= site_url('filings/new') ?>">Submit your first filing</a>.
  </div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Type</th>
          <th>Dates</th>
          <th>Details</th>
          <th>Approver</th>
          <th>Status</th>
          <th>Filed</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($filings as $f): ?>
          <tr>
            <td><strong><?= esc($typeLabels[$f['filing_type']] ?? $f['filing_type']) ?></strong></td>
            <td><?= $fmtDates($f['dates']) ?></td>
            <td>
              <?php if ($f['filing_type'] === 'leave'): ?>
                <?= esc($f['leave_type_name'] ?? '—') ?>
              <?php elseif ($f['filing_type'] === 'schedule_change'): ?>
                <?= esc($f['requested_schedule_name'] ?? '—') ?>
              <?php elseif (in_array($f['filing_type'], ['official_business', 'time_adjustment'], true)): ?>
                <?= esc(trim(($f['requested_time_in'] ?? '') . ' – ' . ($f['requested_time_out'] ?? ''), ' –')) ?: '—' ?>
              <?php else: ?>
                —
              <?php endif; ?>
              <?php if (! empty($f['reason'])): ?>
                <div class="muted"><?= esc($f['reason']) ?></div>
              <?php endif; ?>
            </td>
            <td><?= esc($f['approver_name'] ?? '—') ?></td>
            <td><?= $statusBadge($f['status']) ?></td>
            <td class="muted"><?= esc($f['filed_at'] ? date('M j, Y', strtotime($f['filed_at'])) : '—') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?= $this->endSection() ?>
