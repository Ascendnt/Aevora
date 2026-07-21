<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$typeLabels = [
    'leave'             => 'Leave',
    'official_business' => 'Official business',
    'schedule_change'   => 'Schedule change',
    'time_adjustment'   => 'Time adjustment',
];

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
    <h1>My approvals</h1>
    <p class="sub"><a href="<?= site_url('filings') ?>">&larr; Back to my filings</a></p>
  </div>
</div>

<?php if (! $employee): ?>
  <div class="empty">Only employees can approve filings — superadmin accounts are not anyone's supervisor.</div>
<?php elseif (empty($approvals)): ?>
  <div class="empty">Nothing waiting on your decision right now.</div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Employee</th>
          <th>Type</th>
          <th>Dates</th>
          <th>Details</th>
          <th style="width:1%;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($approvals as $a): ?>
          <tr>
            <td><strong><?= esc($a['employee_name']) ?></strong></td>
            <td><?= esc($typeLabels[$a['filing_type']] ?? $a['filing_type']) ?></td>
            <td><?= $fmtDates($a['dates']) ?></td>
            <td>
              <?php if ($a['filing_type'] === 'leave'): ?>
                <?= esc($a['leave_type_name'] ?? '—') ?>
              <?php elseif ($a['filing_type'] === 'schedule_change'): ?>
                <?= esc($a['requested_schedule_name'] ?? '—') ?>
              <?php elseif (in_array($a['filing_type'], ['official_business', 'time_adjustment'], true)): ?>
                <?= esc(trim(($a['requested_time_in'] ?? '') . ' – ' . ($a['requested_time_out'] ?? ''), ' –')) ?: '—' ?>
              <?php endif; ?>
              <?php if (! empty($a['reason'])): ?>
                <div class="muted"><?= esc($a['reason']) ?></div>
              <?php endif; ?>
            </td>
            <td style="white-space:nowrap;">
              <form method="post" action="<?= site_url('filings/' . $a['id'] . '/decide') ?>" style="display:flex; gap:6px; align-items:center;">
                <?= csrf_field() ?>
                <input type="text" name="note" placeholder="Optional note" style="width:160px; padding:8px 10px;">
                <button type="submit" name="decision" value="approved" class="btn sm primary">Approve</button>
                <button type="submit" name="decision" value="rejected" class="btn sm danger"
                        onclick="return confirm('Reject this filing?');">Reject</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?= $this->endSection() ?>
