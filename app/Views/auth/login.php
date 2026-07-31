<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php $brand = hq_company_name(); ?>
  <title>Sign in · <?= esc($brand) ?></title>
  <?= $this->include('partials/head_assets') ?>
</head>
<body class="min-h-screen grid place-items-center p-6">

  <div class="w-full max-w-[960px] grid md:grid-cols-[0.85fr_1fr] rounded-[24px] overflow-hidden shadow-float border border-line bg-surface">

    <!-- brand aside -->
    <aside class="relative hidden md:flex flex-col justify-between p-11 text-white overflow-hidden"
           style="background:linear-gradient(155deg,var(--accent-deep) 0%,var(--accent) 100%);">
      <!-- drifting botanical light -->
      <span class="absolute -top-24 -left-16 w-72 h-72 rounded-full blur-3xl opacity-30 animate-drift" style="background:#ffffff;"></span>
      <div class="relative flex items-center gap-3 font-serif text-[24px] font-semibold">
        <span class="grid place-items-center w-10 h-10 rounded-[12px]" style="background:rgba(255,255,255,.18);">
          <svg viewBox="0 0 40 40" width="26" height="26" fill="none">
            <circle cx="20" cy="20" r="17" stroke="#fff" stroke-width="1.4" stroke-dasharray="2 5" opacity=".55">
              <animateTransform attributeName="transform" type="rotate" from="0 20 20" to="360 20 20" dur="40s" repeatCount="indefinite"/>
            </circle>
            <path d="M20 8C15 12 12.5 15.5 12.5 20a7.5 7.5 0 0 0 15 0C27.5 15.5 25 12 20 8Z" fill="#fff" opacity=".95"/>
            <path d="M20 27V17" stroke="var(--accent-deep)" stroke-width="1.7" stroke-linecap="round"/>
            <circle cx="20" cy="4" r="1.6" fill="#f0dccf"/>
          </svg>
        </span>
        <?= esc($brand) ?>
      </div>

      <div class="relative">
        <p class="text-[11px] font-semibold uppercase tracking-eyebrow text-white/70 mb-4">People-first HR</p>
        <p class="font-serif text-[32px] font-medium leading-[1.15] tracking-[-.02em]">People-first HR,<br>grown with care.</p>
        <p class="text-[14px] leading-relaxed text-white/85 mt-4 max-w-[34ch]">Manage your companies, branches, and teams from one calm, considered workspace.</p>
      </div>

      <svg class="absolute -right-14 -bottom-16 w-64 opacity-[.12]" viewBox="0 0 200 200" fill="#fff" aria-hidden="true">
        <path d="M100 15c-42 32-62 64-62 105a62 62 0 0 0 124 0c0-41-20-73-62-105Z"/>
        <path d="M100 190V70" stroke="var(--accent-deep)" stroke-width="4" fill="none"/>
      </svg>
    </aside>

    <!-- form -->
    <div class="bg-surface px-8 py-12 sm:px-12 flex flex-col justify-center">
      <!-- mobile brand -->
      <div class="md:hidden flex items-center gap-3 font-serif text-[20px] font-semibold mb-8">
        <span class="grid place-items-center w-9 h-9 rounded-[11px] bg-sage text-white">
          <?= icon('leaf', '', 20) ?>
        </span>
        <?= esc($brand) ?>
      </div>

      <p class="eyebrow mb-3">Welcome back</p>
      <h1 class="font-serif text-[27px] font-medium tracking-[-.015em] mb-1">Sign in to your workspace</h1>
      <p class="sub mb-8">Enter your details to pick up where you left off.</p>

      <?php if (session()->getFlashdata('error')): ?>
        <div class="alert error"><?= esc(session()->getFlashdata('error')) ?></div>
      <?php endif; ?>

      <form method="post" action="<?= site_url('login') ?>" class="flex flex-col gap-4">
        <?= csrf_field() ?>
        <div>
          <label for="email">Email</label>
          <input type="email" id="email" name="email" value="<?= esc(old('email')) ?>" placeholder="you@company.com" required autofocus>
        </div>
        <div>
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn primary w-full justify-center mt-2" style="padding:.78rem;">
          Sign in <?= icon('arrow-right', '', 18) ?>
        </button>
      </form>

      <p class="text-[12px] text-ink-mute mt-8 text-center">Protected workspace · <?= esc($brand) ?></p>
    </div>
  </div>
</body>
</html>
