<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1>Employee ranks</h1>
    <p class="sub"><a href="<?= site_url('employee-management') ?>">&larr; Back to employee management</a></p>
  </div>
  <a class="btn primary" href="<?= site_url('employee-ranks/new' . ($filter ? '?company=' . $filter : '')) ?>"><i class="ti ti-plus" aria-hidden="true"></i>Add rank</a>
</div>

<?php if (count($companies) > 1): ?>
  <form method="get" action="<?= site_url('employee-ranks') ?>" style="margin-bottom:1rem; max-width:320px;">
    <label for="company">Filter by company</label>
    <select id="company" name="company" onchange="this.form.submit()">
      <option value="">All companies</option>
      <?php foreach ($companies as $c): ?>
        <option value="<?= esc($c['id']) ?>" <?= (int) $filter === (int) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </form>
<?php endif; ?>

<?php if (empty($employeeRanks)): ?>
  <div class="empty">
    No employee ranks yet. <a href="<?= site_url('employee-ranks/new') ?>">Add a rank</a> to get started.
  </div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Company</th>
          <th>Exempt from late/undertime deductions</th>
          <th>Sort order</th>
          <th style="width:1%;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($employeeRanks as $er): ?>
          <tr>
            <td><a href="<?= site_url('employee-ranks/' . $er['id'] . '/edit') ?>"><strong><?= esc($er['name']) ?></strong></a></td>
            <td><?= esc($er['company_name']) ?></td>
            <td><span class="badge <?= db_bool($er['is_exempt']) ? 'active' : 'inactive' ?>"><?= db_bool($er['is_exempt']) ? 'Exempt' : 'Not exempt' ?></span></td>
            <td><?= esc($er['sort_order']) ?></td>
            <td style="white-space:nowrap;">
              <a class="btn sm" href="<?= site_url('employee-ranks/' . $er['id'] . '/edit') ?>">Edit</a>
              <form method="post" action="<?= site_url('employee-ranks/' . $er['id'] . '/delete') ?>" style="display:inline;"
                    onsubmit="return confirm('Delete rank <?= esc($er['name'], 'js') ?>?');">
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
