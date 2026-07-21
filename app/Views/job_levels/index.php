<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1>Job levels</h1>
    <p class="sub"><a href="<?= site_url('employee-management') ?>">&larr; Back to employee management</a></p>
  </div>
  <a class="btn primary" href="<?= site_url('job-levels/new' . ($filter ? '?company=' . $filter : '')) ?>"><i class="ti ti-plus" aria-hidden="true"></i>Add job level</a>
</div>

<?php if (count($companies) > 1): ?>
  <form method="get" action="<?= site_url('job-levels') ?>" style="margin-bottom:1rem; max-width:320px;">
    <label for="company">Filter by company</label>
    <select id="company" name="company" onchange="this.form.submit()">
      <option value="">All companies</option>
      <?php foreach ($companies as $c): ?>
        <option value="<?= esc($c['id']) ?>" <?= (int) $filter === (int) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </form>
<?php endif; ?>

<?php if (empty($jobLevels)): ?>
  <div class="empty">
    No job levels yet. <a href="<?= site_url('job-levels/new') ?>">Add a job level</a> to get started.
  </div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Company</th>
          <th>Sort order</th>
          <th style="width:1%;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($jobLevels as $jl): ?>
          <tr>
            <td><a href="<?= site_url('job-levels/' . $jl['id'] . '/edit') ?>"><strong><?= esc($jl['name']) ?></strong></a></td>
            <td><?= esc($jl['company_name']) ?></td>
            <td><?= esc($jl['sort_order']) ?></td>
            <td style="white-space:nowrap;">
              <a class="btn sm" href="<?= site_url('job-levels/' . $jl['id'] . '/edit') ?>">Edit</a>
              <form method="post" action="<?= site_url('job-levels/' . $jl['id'] . '/delete') ?>" style="display:inline;"
                    onsubmit="return confirm('Delete job level <?= esc($jl['name'], 'js') ?>?');">
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
