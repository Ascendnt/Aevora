<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php $brand = hq_company_name(); ?>
  <title>Sign in · <?= esc($brand) ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tabler-icons/3.31.0/tabler-icons.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Bricolage+Grotesque:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
  <script>var t=document.cookie.match(/hris_theme=(\w+)/);if(t&&t[1]==='dark')document.documentElement.setAttribute('data-theme','dark');</script>
</head>
<body>
<div class="login-wrap">
  <div class="login-split">
    <aside class="login-aside">
      <div class="la-brand">
        <span class="brand-mark" style="width:40px;height:40px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;">
          <svg viewBox="0 0 40 40" width="26" height="26" fill="none">
            <circle cx="20" cy="20" r="17" stroke="#fff" stroke-width="1.4" stroke-dasharray="2 5" opacity=".55">
              <animateTransform attributeName="transform" type="rotate" from="0 20 20" to="360 20 20" dur="40s" repeatCount="indefinite"/>
            </circle>
            <path d="M20 8C15 12 12.5 15.5 12.5 20a7.5 7.5 0 0 0 15 0C27.5 15.5 25 12 20 8Z" fill="#fff" opacity=".95">
              <animate attributeName="opacity" values=".78;1;.78" dur="4s" repeatCount="indefinite"/>
            </path>
            <path d="M20 27V17" stroke="var(--sage-deep)" stroke-width="1.7" stroke-linecap="round"/>
            <circle cx="20" cy="4" r="1.6" fill="#f0dccf"/>
          </svg>
        </span>
        <?= esc($brand) ?>
      </div>
      <div>
        <p class="la-head">People-first HR,<br>grown with care.</p>
        <p class="la-sub">Manage your companies, branches, and teams from one calm, considered workspace.</p>
      </div>
      <svg class="la-leaf" viewBox="0 0 200 200" fill="#fff"><path d="M100 15c-42 32-62 64-62 105a62 62 0 0 0 124 0c0-41-20-73-62-105Z"/></svg>
    </aside>

    <div class="login-form">
      <h1>Welcome back</h1>
      <p class="sub">Sign in to your workspace</p>

      <?php if (session()->getFlashdata('error')): ?>
        <div class="alert error"><?= esc(session()->getFlashdata('error')) ?></div>
      <?php endif; ?>

      <form method="post" action="<?= site_url('login') ?>">
        <?= csrf_field() ?>
        <div class="field">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" value="<?= esc(old('email')) ?>" placeholder="you@company.com" required autofocus>
        </div>
        <div class="field">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn primary" style="width:100%; justify-content:center; margin-top:10px; padding:12px;">
          Sign in <i class="ti ti-arrow-right"></i>
        </button>
      </form>
    </div>
  </div>
</div>
</body>
</html>