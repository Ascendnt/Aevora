<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$statusBadge = static function (string $status): string {
    $map = ['draft' => 'inactive', 'final' => 'active', 'signed' => 'active', 'archived' => 'inactive'];
    return $map[$status] ?? 'inactive';
};
?>

<div class="page-head">
  <div>
    <h1>Documents — <?= esc($employee['user_name']) ?></h1>
    <p class="sub"><a href="<?= site_url('employee-management/' . $employee['id'] . '/edit') ?>">&larr; Back to <?= esc($employee['user_name']) ?>'s profile</a></p>
  </div>
</div>

<div class="form-grid">
  <div class="form-card">
    <p class="section-label">Generate from template</p>
    <?php if (empty($templatesByType)): ?>
      <p class="muted">No active templates yet. <a href="<?= site_url('document-templates') ?>">Add one</a> first.</p>
    <?php else: ?>
      <form method="post" action="<?= site_url('employee-management/' . $employee['id'] . '/documents/generate') ?>">
        <?= csrf_field() ?>
        <label for="gen_document_type_id">Document type</label>
        <select id="gen_document_type_id" name="document_type_id" onchange="filterTemplates(this.value)" required>
          <option value="">Choose a type…</option>
          <?php foreach ($types as $t): ?>
            <?php if (! empty($templatesByType[$t['id']])): ?>
              <option value="<?= esc($t['id']) ?>"><?= esc($t['name']) ?></option>
            <?php endif; ?>
          <?php endforeach; ?>
        </select>

        <label for="gen_document_template_id" style="margin-top:12px; display:block;">Template</label>
        <select id="gen_document_template_id" name="document_template_id" required>
          <option value="">Choose a document type first…</option>
          <?php foreach ($templatesByType as $typeId => $rows): ?>
            <?php foreach ($rows as $tpl): ?>
              <option value="<?= esc($tpl['id']) ?>" data-type="<?= esc($typeId) ?>" style="display:none;">
                <?= esc($tpl['name']) ?><?= $tpl['company_id'] === null ? ' (system-wide)' : '' ?>
              </option>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </select>

        <label for="gen_title" style="margin-top:12px; display:block;">Title (optional)</label>
        <input type="text" id="gen_title" name="title" placeholder="Defaults to type — employee name">

        <div class="form-actions">
          <button type="submit" class="btn primary">Generate draft</button>
        </div>
      </form>
    <?php endif; ?>
  </div>

  <div class="form-card">
    <p class="section-label">Upload a file</p>
    <form method="post" action="<?= site_url('employee-management/' . $employee['id'] . '/documents/upload') ?>" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <label for="up_document_type_id">Document type</label>
      <select id="up_document_type_id" name="document_type_id" required>
        <option value="">Choose a type…</option>
        <?php foreach ($types as $t): ?>
          <option value="<?= esc($t['id']) ?>"><?= esc($t['name']) ?></option>
        <?php endforeach; ?>
      </select>

      <label for="up_title" style="margin-top:12px; display:block;">Title (optional)</label>
      <input type="text" id="up_title" name="title" placeholder="Defaults to type — employee name">

      <label for="up_file" style="margin-top:12px; display:block;">File</label>
      <input type="file" id="up_file" name="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
      <p class="muted" style="margin-top:4px;">Allowed: PDF, DOC, DOCX, JPG, PNG.</p>

      <div class="form-actions">
        <button type="submit" class="btn primary">Upload</button>
      </div>
    </form>
  </div>
</div>

<p class="section-label" style="margin-top:1.5rem;">Documents on file</p>

<?php if (empty($documents)): ?>
  <div class="empty">No documents yet for this employee.</div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Title</th>
          <th>Type</th>
          <th>Source</th>
          <th>Status</th>
          <th>Issued</th>
          <th style="width:1%;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($documents as $d): ?>
          <tr>
            <td><strong><?= esc($d['title']) ?></strong></td>
            <td><?= esc($d['type_name']) ?></td>
            <td>
              <?php if (! empty($d['file_path'])): ?>
                Uploaded file
              <?php elseif (! empty($d['template_name'])): ?>
                Generated — <?= esc($d['template_name']) ?>
              <?php else: ?>
                Generated
              <?php endif; ?>
            </td>
            <td><span class="badge <?= esc($statusBadge($d['status'])) ?>"><?= esc(ucfirst($d['status'])) ?></span></td>
            <td><?= esc($d['issued_date'] ?? '—') ?></td>
            <td style="white-space:nowrap;">
              <a class="btn sm" href="<?= site_url('documents/' . $d['id'] . '/view') ?>" target="_blank" rel="noopener">View</a>

              <form method="post" action="<?= site_url('documents/' . $d['id'] . '/status') ?>" style="display:inline;">
                <?= csrf_field() ?>
                <select name="status" onchange="this.form.submit()" style="width:auto; display:inline-block; padding:4px 8px;">
                  <?php foreach (['draft', 'final', 'signed', 'archived'] as $s): ?>
                    <option value="<?= $s ?>" <?= $d['status'] === $s ? 'selected' : '' ?>><?= esc(ucfirst($s)) ?></option>
                  <?php endforeach; ?>
                </select>
              </form>

              <form method="post" action="<?= site_url('documents/' . $d['id'] . '/delete') ?>" style="display:inline;"
                    onsubmit="return confirm('Delete <?= esc($d['title'], 'js') ?>?');">
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

<script>
function filterTemplates(typeId) {
  var select = document.getElementById('gen_document_template_id');
  var options = select.querySelectorAll('option[data-type]');
  var firstVisible = null;
  options.forEach(function (opt) {
    var match = opt.getAttribute('data-type') === typeId;
    opt.style.display = match ? '' : 'none';
    if (match && !firstVisible) firstVisible = opt;
  });
  select.value = firstVisible ? firstVisible.value : '';
}
</script>

<?= $this->endSection() ?>
