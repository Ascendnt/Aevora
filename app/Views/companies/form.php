<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$isEdit = $company !== null;
$val    = static fn (string $key) => esc(old($key, $company[$key] ?? ''));
?>

<div class="page-head">
  <div>
    <h1><?= $isEdit ? 'Edit company' : 'Add company' ?></h1>
    <p class="sub"><a href="<?= site_url('companies') ?>">&larr; Back to company settings</a></p>
  </div>
</div>

  <?php if ($isEdit): ?>
<div class="tabs">
  <a class="active" href="<?= site_url('companies/' . $company['id'] . '/edit') ?>">Profile</a>
  <a href="<?= site_url('companies/' . $company['id'] . '/organization') ?>">Organizational structure</a>
</div>
<?php endif; ?>

<div class="form-card">
  <form method="post" enctype="multipart/form-data"
        action="<?= $isEdit ? site_url('companies/' . $company['id']) : site_url('companies') ?>">
    <?= csrf_field() ?>

    <p class="section-label">Basic company information</p>
    <div class="form-grid">
      <div>
        <label for="name">Company / Trade name *</label>
        <input type="text" id="name" name="name" value="<?= $val('name') ?>" required>
      </div>
      <div>
        <label for="legal_name">Registered / Legal name</label>
        <input type="text" id="legal_name" name="legal_name" value="<?= $val('legal_name') ?>">
      </div>
      <div>
        <label for="industry">Industry / Business type</label>
        <input type="text" id="industry" name="industry" value="<?= $val('industry') ?>">
      </div>
      <div>
        <label for="date_established">Date established</label>
        <input type="date" id="date_established" name="date_established" value="<?= $val('date_established') ?>">
      </div>
      <div>
        <label for="company_size">Company size (no. of employees)</label>
        <input type="number" id="company_size" name="company_size" min="1" value="<?= $val('company_size') ?>">
      </div>
      <div class="full">
        <label for="logo">Company logo</label>
          <?php if ($isEdit && ! empty($company['logo_path'])): ?>
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
              <img src="<?= base_url($company['logo_path']) ?>" alt="Current logo"
                  style="height:48px; border-radius:6px;">
              <button type="submit" class="btn sm danger"
                      form="delete-logo-form"
                      onclick="return confirm('Remove the current logo?');">
                <i class="ti ti-trash" aria-hidden="true"></i> Remove logo
              </button>
            </div>
          <?php endif; ?>
        <input type="file" id="logo" name="logo" accept="image/png,image/jpeg,image/webp">
      </div>
    </div>

    <?php if (is_superadmin()): ?>
    <div class="form-grid" style="margin-top:1rem;">
      <div class="full" style="display:flex; align-items:center; gap:8px;">
        <input type="checkbox" id="is_hq" name="is_hq" value="1" <?= ! empty($company['is_hq']) ? 'checked' : '' ?> style="width:auto;">
        <label for="is_hq" style="margin:0;">Mark as HQ / main company (its name becomes the sidebar brand for everyone)</label>
      </div>
    </div>
    <?php endif; ?>

    <p class="section-label" style="margin-top:1.5rem;">Government registration (Philippines)</p>
    <div class="form-grid">
      <div>
        <label for="sec_dti_number">SEC / DTI Registration No.</label>
        <input type="text" id="sec_dti_number" name="sec_dti_number" value="<?= $val('sec_dti_number') ?>">
      </div>
      <div>
        <label for="tin">BIR TIN</label>
        <input type="text" id="tin" name="tin" value="<?= $val('tin') ?>">
      </div>
      <div>
        <label for="sss_number">SSS Employer No.</label>
        <input type="text" id="sss_number" name="sss_number" value="<?= $val('sss_number') ?>">
      </div>
      <div>
        <label for="philhealth_number">PhilHealth Employer No.</label>
        <input type="text" id="philhealth_number" name="philhealth_number" value="<?= $val('philhealth_number') ?>">
      </div>
      <div>
        <label for="pagibig_number">Pag-IBIG (HDMF) Employer No.</label>
        <input type="text" id="pagibig_number" name="pagibig_number" value="<?= $val('pagibig_number') ?>">
      </div>
      <div>
        <label for="business_permit_number">Business / Mayor's Permit No.</label>
        <input type="text" id="business_permit_number" name="business_permit_number" value="<?= $val('business_permit_number') ?>">
      </div>
      <div>
        <label for="rdo_code">RDO Code</label>
        <input type="text" id="rdo_code" name="rdo_code" value="<?= $val('rdo_code') ?>">
      </div>
    </div>

    <p class="section-label" style="margin-top:1.5rem;">Address &amp; contact</p>
    <div class="form-grid">
      <div class="full">
        <label for="address_line">Registered / Head office address</label>
        <input type="text" id="address_line" name="address_line" value="<?= $val('address_line') ?>">
      </div>
      <div>
        <label for="city">City</label>
        <input type="text" id="city" name="city" value="<?= $val('city') ?>">
      </div>
      <div>
        <label for="province">Province / State</label>
        <input type="text" id="province" name="province" value="<?= $val('province') ?>">
      </div>
      <div>
        <label for="postal_code">Postal code</label>
        <input type="text" id="postal_code" name="postal_code" value="<?= $val('postal_code') ?>">
      </div>
      <div>
        <label for="country">Country</label>
        <input type="text" id="country" name="country" value="<?= esc(old('country', $company['country'] ?? 'Philippines')) ?>">
      </div>
      <div>
        <label for="phone">Contact number</label>
        <input type="text" id="phone" name="phone" value="<?= $val('phone') ?>">
      </div>
      <div>
        <label for="email">Company email</label>
        <input type="email" id="email" name="email" value="<?= $val('email') ?>">
      </div>
      <div class="full">
        <label for="website">Website</label>
        <input type="text" id="website" name="website" value="<?= $val('website') ?>">
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn primary"><?= $isEdit ? 'Save changes' : 'Add company' ?></button>
      <a class="btn" href="<?= site_url('companies') ?>">Cancel</a>
    </div>

 

  </form>
</div>


     <?php if ($isEdit && ! empty($company['logo_path'])): ?>
        <form id="delete-logo-form" method="post"
              action="<?= site_url('companies/' . $company['id'] . '/logo/delete') ?>">
          <?= csrf_field() ?>
        </form>
      <?php endif; ?>



<?= $this->endSection() ?>