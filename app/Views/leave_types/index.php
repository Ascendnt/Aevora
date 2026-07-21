<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1>Leave types</h1>
    <p class="sub"><a href="<?= site_url('filings') ?>">&larr; Back to filings</a></p>
  </div>
  <div style="display:flex; gap:10px;">
    <a class="btn" href="<?= site_url('leave-types/import') ?>"><i class="ti ti-upload" aria-hidden="true"></i>Import CSV</a>
    <a class="btn primary" href="<?= site_url('leave-types/new') ?>"><i class="ti ti-plus" aria-hidden="true"></i>Add leave type</a>
  </div>
</div>

<form method="get" action="<?= site_url('leave-types') ?>" style="margin-bottom:1rem; max-width:320px;">
  <label for="company">Filter by company</label>
  <select id="company" name="company" onchange="this.form.submit()">
    <option value="">All companies</option>
    <?php foreach ($companies as $c): ?>
      <option value="<?= esc($c['id']) ?>" <?= (int) $filter === (int) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
    <?php endforeach; ?>
  </select>
</form>

<?php if (empty($leaveTypes)): ?>
  <div class="empty">
    No leave types found. <a href="<?= site_url('leave-types/new') ?>">Add a leave type</a>.
  </div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Company</th>
          <th>Paid?</th>
          <th>Filing rule</th>
          <th>Min. notice</th>
          <th style="width:1%;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($leaveTypes as $lt): ?>
          <tr>
            <td><strong><?= esc($lt['name']) ?></strong></td>
            <td><?= esc($lt['company_name']) ?></td>
            <td><span class="badge <?= db_bool($lt['is_paid']) ? 'active' : 'inactive' ?>"><?= db_bool($lt['is_paid']) ? 'Paid' : 'Unpaid' ?></span></td>
            <td><?= esc(ucfirst($lt['filing_rule'])) ?></td>
            <td><?= (int) $lt['min_days_notice'] ?> day(s)</td>
            <td style="white-space:nowrap;">
              <a class="btn sm" href="<?= site_url('leave-types/' . $lt['id'] . '/edit') ?>">Edit</a>
              <form method="post" action="<?= site_url('leave-types/' . $lt['id'] . '/delete') ?>" style="display:inline;"
                    onsubmit="return confirm('Delete leave type <?= esc($lt['name'], 'js') ?>?');">
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
