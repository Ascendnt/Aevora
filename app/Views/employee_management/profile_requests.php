<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$fieldLabels = [
    'date_of_birth'           => 'Date of birth',
    'phone'                   => 'Phone number',
    'address'                 => 'Address',
    'emergency_contact_name'  => 'Emergency contact name',
    'emergency_contact_phone' => 'Emergency contact phone',
];
?>

<div class="page-head">
  <div>
    <h1>Profile change requests</h1>
    <p class="sub"><a href="<?= site_url('employee-management') ?>">&larr; Back to employee management</a></p>
  </div>
</div>

<?php if (empty($requests)): ?>
  <div class="empty">No pending profile change requests.</div>
<?php else: ?>
  <?php foreach ($requests as $r): ?>
    <?php $changes = json_decode((string) $r['requested_changes'], true) ?: []; ?>
    <div class="form-card" style="max-width:none; margin-bottom:16px;">
      <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px;">
        <div>
          <p style="margin:0 0 4px; font-weight:600;"><?= esc($r['employee_name']) ?> <span class="muted" style="font-weight:400;">&middot; <?= esc($r['company_name']) ?></span></p>
          <p class="muted" style="margin:0;">Submitted <?= esc(date('M j, Y g:ia', strtotime($r['created_at']))) ?></p>
          <?php if (! empty($r['employee_note'])): ?><p style="margin:8px 0 0;"><?= esc($r['employee_note']) ?></p><?php endif; ?>
        </div>
      </div>

      <div class="table-wrap" style="margin-top:12px;">
        <table>
          <thead><tr><th>Field</th><th>Current</th><th>Requested</th></tr></thead>
          <tbody>
            <?php foreach ($changes as $field => $change): ?>
              <tr>
                <td><?= esc($fieldLabels[$field] ?? $field) ?></td>
                <td class="muted"><?= esc($change['from'] ?: '—') ?></td>
                <td><strong><?= esc($change['to'] ?: '—') ?></strong></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div style="display:flex; gap:16px; margin-top:14px; flex-wrap:wrap;">
        <form method="post" action="<?= site_url('employee-management/profile-requests/' . $r['id'] . '/approve') ?>" style="display:flex; gap:8px; align-items:center;">
          <?= csrf_field() ?>
          <input type="text" name="review_note" placeholder="Note (optional)" style="max-width:220px;">
          <button type="submit" class="btn primary sm">Approve &amp; apply</button>
        </form>
        <form method="post" action="<?= site_url('employee-management/profile-requests/' . $r['id'] . '/reject') ?>" style="display:flex; gap:8px; align-items:center;"
              onsubmit="return confirm('Reject this profile change request?');">
          <?= csrf_field() ?>
          <input type="text" name="review_note" placeholder="Reason (optional)" style="max-width:220px;">
          <button type="submit" class="btn sm danger">Reject</button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<?= $this->endSection() ?>
