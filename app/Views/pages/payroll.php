<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1>Payroll</h1>
    <p class="sub">Sample data — this module is not wired to the database yet</p>
  </div>
</div>

<div class="stat-grid">
  <div class="stat"><p class="label">Next payroll run</p><p class="value">Jul 31</p></div>
  <div class="stat"><p class="label">Pay period</p><p class="value">Jul 16–31</p></div>
  <div class="stat"><p class="label">Employees in run</p><p class="value">128</p></div>
</div>

<div class="table-wrap">
  <table>
    <thead><tr><th>Run</th><th>Period</th><th>Employees</th><th>Status</th></tr></thead>
    <tbody>
      <tr><td>2026-07-B</td><td>Jul 16 – Jul 31</td><td>128</td><td><span class="badge inactive">Draft</span></td></tr>
      <tr><td>2026-07-A</td><td>Jul 1 – Jul 15</td><td>127</td><td><span class="badge active">Released</span></td></tr>
    </tbody>
  </table>
</div>

<?= $this->endSection() ?>
