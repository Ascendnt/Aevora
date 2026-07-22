<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1>My profile</h1>
    <p class="sub">Your details on file &mdash; contact info can be updated by request</p>
  </div>
</div>

<?php if ($pending): ?>
  <div class="form-card" style="max-width:none; margin-bottom:20px; border-color:var(--border-accent, var(--accent));">
    <p class="section-label" style="margin-top:0;">Pending change request</p>
    <p class="muted">Submitted <?= esc(date('M j, Y g:ia', strtotime($pending['created_at']))) ?> &mdash; awaiting HR review.</p>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Field</th><th>Current</th><th>Requested</th></tr></thead>
        <tbody>
          <?php foreach ($pendingChanges as $field => $change): ?>
            <tr>
              <td><?= esc($fieldLabels[$field] ?? $field) ?></td>
              <td class="muted"><?= esc($change['from'] ?: '—') ?></td>
              <td><strong><?= esc($change['to'] ?: '—') ?></strong></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<p class="section-label">Employment details</p>
<div class="form-card" style="max-width:none; margin-bottom:20px;">
  <div class="form-grid" style="row-gap:14px;">
    <div><label style="font-weight:600;">Name</label><p class="muted" style="margin:2px 0 0;"><?= esc($employee['user_name']) ?></p></div>
    <div><label style="font-weight:600;">Email</label><p class="muted" style="margin:2px 0 0;"><?= esc($employee['user_email']) ?></p></div>
    <div><label style="font-weight:600;">Employee number</label><p class="muted" style="margin:2px 0 0;"><?= esc($employee['employee_number'] ?: '—') ?></p></div>
    <div><label style="font-weight:600;">Company</label><p class="muted" style="margin:2px 0 0;"><?= esc($employee['company_name']) ?><?= $employee['branch_name'] ? ' · ' . esc($employee['branch_name']) : '' ?></p></div>
    <div><label style="font-weight:600;">Department</label><p class="muted" style="margin:2px 0 0;"><?= esc($employee['department_name'] ?: '—') ?></p></div>
    <div><label style="font-weight:600;">Position</label><p class="muted" style="margin:2px 0 0;"><?= esc($employee['position_title'] ?: '—') ?></p></div>
    <div><label style="font-weight:600;">Job level</label><p class="muted" style="margin:2px 0 0;"><?= esc($employee['job_level_name'] ?: '—') ?></p></div>
    <div><label style="font-weight:600;">Rank</label><p class="muted" style="margin:2px 0 0;"><?= esc($employee['employee_rank_name'] ?: '—') ?></p></div>
    <div><label style="font-weight:600;">Reports to</label><p class="muted" style="margin:2px 0 0;"><?= esc($employee['supervisor_name'] ?: '—') ?></p></div>
    <div><label style="font-weight:600;">Work schedule</label><p class="muted" style="margin:2px 0 0;"><?= esc($employee['work_schedule_name'] ?: '—') ?></p></div>
    <div><label style="font-weight:600;">Hire date</label><p class="muted" style="margin:2px 0 0;"><?= $employee['hire_date'] ? esc(date('M j, Y', strtotime($employee['hire_date']))) : '—' ?></p></div>
    <div><label style="font-weight:600;">Status</label><p class="muted" style="margin:2px 0 0;"><span class="badge <?= $employee['status'] === 'active' ? 'active' : 'inactive' ?>"><?= esc(ucfirst($employee['status'])) ?></span></p></div>
  </div>
  <p class="muted" style="margin-top:14px;">These details are managed by HR. If any of this looks wrong, contact HR directly or flag it via a filing.</p>
</div>

<p class="section-label">Contact details</p>
<div class="form-card" style="max-width:none;">
  <?php if ($pending): ?>
    <div class="empty">You have a pending request above — submit a new one once it's been reviewed.</div>
  <?php else: ?>
    <form method="post" action="<?= site_url('my-profile/request-edit') ?>">
      <?= csrf_field() ?>
      <div class="form-grid">
        <div>
          <label for="date_of_birth">Date of birth</label>
          <input type="date" id="date_of_birth" name="date_of_birth" value="<?= esc(old('date_of_birth', $employee['date_of_birth'] ?? '')) ?>">
        </div>
        <div>
          <label for="phone">Phone number</label>
          <input type="text" id="phone" name="phone" value="<?= esc(old('phone', $employee['phone'] ?? '')) ?>">
        </div>
        <div class="full">
          <label for="address">Address</label>
          <textarea id="address" name="address" rows="2"><?= esc(old('address', $employee['address'] ?? '')) ?></textarea>
        </div>
        <div>
          <label for="emergency_contact_name">Emergency contact name</label>
          <input type="text" id="emergency_contact_name" name="emergency_contact_name" value="<?= esc(old('emergency_contact_name', $employee['emergency_contact_name'] ?? '')) ?>">
        </div>
        <div>
          <label for="emergency_contact_phone">Emergency contact phone</label>
          <input type="text" id="emergency_contact_phone" name="emergency_contact_phone" value="<?= esc(old('emergency_contact_phone', $employee['emergency_contact_phone'] ?? '')) ?>">
        </div>
        <div class="full">
          <label for="employee_note">Note to HR (optional)</label>
          <textarea id="employee_note" name="employee_note" rows="2" placeholder="Any context for the reviewer"><?= esc(old('employee_note')) ?></textarea>
        </div>
      </div>
      <p class="muted" style="margin:8px 0 0;">Changes here are submitted for HR review, not applied immediately.</p>
      <div class="form-actions">
        <button type="submit" class="btn primary">Submit for review</button>
      </div>
    </form>
  <?php endif; ?>
</div>

<?php if (! empty($history)): ?>
  <p class="section-label" style="margin-top:1.5rem;">Request history</p>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Submitted</th><th>Status</th><th>Reviewed</th></tr></thead>
      <tbody>
        <?php foreach ($history as $h): ?>
          <tr>
            <td><?= esc(date('M j, Y', strtotime($h['created_at']))) ?></td>
            <td><span class="badge <?= $h['status'] === 'approved' ? 'active' : ($h['status'] === 'pending' ? 'inactive' : '') ?>" <?= $h['status'] === 'rejected' ? 'style="background:var(--bg-danger); color:var(--text-danger);"' : '' ?>><?= esc(ucfirst($h['status'])) ?></span></td>
            <td class="muted"><?= $h['reviewed_at'] ? esc(date('M j, Y', strtotime($h['reviewed_at']))) : '—' ?><?= $h['review_note'] ? ' — ' . esc($h['review_note']) : '' ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?= $this->endSection() ?>
