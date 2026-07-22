<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $years = range((int) date('Y') + 1, (int) date('Y') - 3); ?>

<div class="page-head">
  <div>
    <h1>Holiday calendar</h1>
    <p class="sub"><a href="<?= site_url('attendance') ?>">&larr; Back to time &amp; attendance</a></p>
  </div>
  <a class="btn primary" href="<?= site_url('holidays/new' . ($filter ? '?company=' . $filter : '')) ?>"><i class="ti ti-plus" aria-hidden="true"></i>Add holiday</a>
</div>

<form method="get" action="<?= site_url('holidays') ?>" style="margin-bottom:1rem; display:flex; gap:14px; flex-wrap:wrap;">
  <?php if (count($companies) > 1): ?>
    <div style="max-width:280px;">
      <label for="company">Company</label>
      <select id="company" name="company" onchange="this.form.submit()">
        <option value="">All companies</option>
        <?php foreach ($companies as $c): ?>
          <option value="<?= esc($c['id']) ?>" <?= (int) $filter === (int) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  <?php endif; ?>
  <div style="max-width:160px;">
    <label for="year">Year</label>
    <select id="year" name="year" onchange="this.form.submit()">
      <?php foreach ($years as $y): ?>
        <option value="<?= esc($y) ?>" <?= (int) $year === $y ? 'selected' : '' ?>><?= esc($y) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
</form>

<?php if ($filter): ?>
  <div class="form-card" style="margin-bottom:20px; max-width:none;">
    <p class="section-label">Sync from the public holiday API</p>
    <form method="post" action="<?= site_url('holidays/sync') ?>" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
      <?= csrf_field() ?>
      <input type="hidden" name="company_id" value="<?= esc($filter) ?>">
      <div>
        <label for="sync_year">Year</label>
        <select id="sync_year" name="year">
          <?php foreach ($years as $y): ?>
            <option value="<?= esc($y) ?>" <?= (int) $year === $y ? 'selected' : '' ?>><?= esc($y) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label for="sync_country">Country code</label>
        <input type="text" id="sync_country" name="country_code" maxlength="2" style="width:80px; text-transform:uppercase;" placeholder="PH">
      </div>
      <button type="submit" class="btn"><i class="ti ti-refresh" aria-hidden="true"></i>Sync holidays</button>
      <p class="muted" style="margin:0; flex-basis:100%;">
        Pulls national public holidays from date.nager.at. Leave the country code blank to use the company's own country. Already-imported dates are skipped, so this is safe to re-run.
        Coverage varies by country &mdash; some countries return few or no holidays.
      </p>
    </form>
    <form method="post" action="<?= site_url('holidays/bulk-delete-source') ?>" style="margin-top:14px; padding-top:14px; border-top:1px solid var(--border);"
          onsubmit="return confirm('Remove every API-synced holiday for <?= esc($year, 'js') ?> for this company? Manually-added holidays are not affected. This cannot be undone.');">
      <?= csrf_field() ?>
      <input type="hidden" name="company_id" value="<?= esc($filter) ?>">
      <input type="hidden" name="year" value="<?= esc($year) ?>">
      <button type="submit" class="btn sm danger"><i class="ti ti-trash" aria-hidden="true"></i>Remove all API-synced holidays for <?= esc($year) ?></button>
      <span class="muted" style="margin-left:10px;">Use this if a sync accidentally pulled the wrong country &mdash; manual entries are untouched.</span>
    </form>
  </div>
<?php endif; ?>

<?php if (empty($holidays)): ?>
  <div class="empty">No holidays recorded for <?= esc($year) ?>. Add one manually or sync from the API above.</div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Date</th>
          <th>Name</th>
          <?php if (count($companies) > 1): ?><th>Company</th><?php endif; ?>
          <th>Type</th>
          <th>Scope</th>
          <th>Source</th>
          <th style="width:1%;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($holidays as $h): ?>
          <tr>
            <td><strong><?= esc(date('M j, Y', strtotime($h['date']))) ?></strong></td>
            <td><a href="<?= site_url('holidays/' . $h['id'] . '/edit') ?>"><?= esc($h['name']) ?></a></td>
            <?php if (count($companies) > 1): ?><td><?= esc($h['company_name']) ?></td><?php endif; ?>
            <td><span class="badge <?= $h['holiday_type'] === 'legal' ? 'active' : 'inactive' ?>"><?= esc(ucfirst($h['holiday_type'])) ?></span></td>
            <td>
              <?= esc(ucfirst($h['scope_type'])) ?>
              <?php if ($h['scope_value']): ?> &mdash; <?= esc($h['scope_value']) ?><?php endif; ?>
              <?php if ($h['scope_type'] === 'branch'): ?> &mdash; <?= esc($h['branch_name'] ?? '— deleted branch —') ?><?php endif; ?>
              <?php if ($h['scope_type'] === 'employee'): ?> &mdash; <?= esc($h['employee_name'] ?? '— deleted employee —') ?><?php endif; ?>
            </td>
            <td class="muted"><?= $h['source'] === 'api_import' ? 'API sync' : 'Manual' ?></td>
            <td style="white-space:nowrap;">
              <a class="btn sm" href="<?= site_url('holidays/' . $h['id'] . '/edit') ?>">Edit</a>
              <form method="post" action="<?= site_url('holidays/' . $h['id'] . '/delete') ?>" style="display:inline;"
                    onsubmit="return confirm('Delete holiday <?= esc($h['name'], 'js') ?>?');">
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
