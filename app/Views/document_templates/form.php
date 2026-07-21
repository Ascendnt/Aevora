<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$isEdit    = $template !== null;
$val       = static fn (string $key, $default = '') => esc(old($key, $template[$key] ?? $default));
$typeSel   = (int) old('document_type_id', $template['document_type_id'] ?? 0);
$isActive  = $isEdit ? db_bool(old('is_active', $template['is_active'])) : (bool) old('is_active', true);
$companyId = old('company_id', $template['company_id'] ?? '');
?>

<div class="page-head">
  <div>
    <h1><?= $isEdit ? 'Edit document template' : 'Add document template' ?></h1>
    <p class="sub"><a href="<?= site_url('document-templates') ?>">&larr; Back to document templates</a></p>
  </div>
</div>

<div class="form-card">
  <form method="post"
        action="<?= $isEdit ? site_url('document-templates/' . $template['id']) : site_url('document-templates') ?>">
    <?= csrf_field() ?>

    <div class="form-grid">
      <div>
        <label for="document_type_id">Document type *</label>
        <select id="document_type_id" name="document_type_id" required>
          <option value="">Choose a type…</option>
          <?php foreach ($types as $t): ?>
            <option value="<?= esc($t['id']) ?>" <?= $typeSel === (int) $t['id'] ? 'selected' : '' ?>><?= esc($t['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label for="name">Template name *</label>
        <input type="text" id="name" name="name" value="<?= $val('name') ?>" required>
      </div>

      <?php if (! empty($companies)): ?>
        <div>
          <label for="company_id">Scope</label>
          <select id="company_id" name="company_id">
            <option value="" <?= $companyId === '' || $companyId === null ? 'selected' : '' ?>>System-wide (all companies)</option>
            <?php foreach ($companies as $c): ?>
              <option value="<?= esc($c['id']) ?>" <?= (int) $companyId === (int) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <p class="muted" style="margin-top:4px;">System-wide templates are visible to every company as a read-only default.</p>
        </div>
      <?php endif; ?>

      <div>
        <label class="check">
          <input type="checkbox" name="is_active" value="1" <?= $isActive ? 'checked' : '' ?>>
          Active
        </label>
        <p class="muted" style="margin-top:4px;">Inactive templates are hidden from the "Generate from template" picker but stay on file.</p>
      </div>

      <div class="full">
        <label for="body">Template body *</label>
        <textarea id="body" name="body" rows="16" style="font-family:monospace;" required><?= $val('body') ?></textarea>
        <p class="muted" style="margin-top:6px;">
          Available tokens: <code>{{employee_name}}</code> <code>{{employee_number}}</code> <code>{{position}}</code>
          <code>{{department}}</code> <code>{{company_name}}</code> <code>{{hire_date}}</code>
          <code>{{basic_pay}}</code> <code>{{today}}</code> — each is replaced with the employee's real data when a document is generated from this template.
        </p>
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn primary"><?= $isEdit ? 'Save changes' : 'Add template' ?></button>
      <a class="btn" href="<?= site_url('document-templates') ?>">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
