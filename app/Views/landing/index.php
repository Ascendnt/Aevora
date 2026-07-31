<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php $brand = hq_company_name(); ?>
<title><?= esc($brand) ?> — HR that moves as one</title>
<?= $this->include('partials/head_assets') ?>

<style type="text/tailwindcss">
  @layer utilities {
    .wrap { @apply relative z-[1] max-w-[1200px] mx-auto px-8; }
  }
  @keyframes ln-rise { to { transform: translateY(0); } }
  @keyframes soft-in { to { opacity: 1; transform: none; } }
  @keyframes draw     { to { stroke-dashoffset: 0; } }
  @keyframes marq     { to { transform: translateX(-50%); } }
  @keyframes bob      { 0%,100%{ transform: translateY(0); } 50%{ transform: translateY(-9px); } }
  @keyframes twinkle  { 0%,100%{ opacity:.5; transform: scale(.9) rotate(0); } 50%{ opacity:1; transform: scale(1.1) rotate(18deg); } }

  .ln { display:block; overflow:hidden; padding-bottom:2px; }
  .ln i { display:inline-block; font-style:normal; transform:translateY(112%); animation: ln-rise .95s cubic-bezier(.6,.05,.15,1) forwards; }
  .fade0 { opacity:0; animation: soft-in .8s .6s forwards; }
  .fade1 { opacity:0; animation: soft-in .8s .78s forwards; }
  .heroart { opacity:0; animation: soft-in 1s .4s forwards; }
  .reveal { opacity:0; transform: translateY(28px); transition: opacity .7s cubic-bezier(.6,.05,.15,1), transform .7s cubic-bezier(.6,.05,.15,1); }
  .reveal.in { opacity:1; transform:none; }
  .vein { stroke-dasharray: 200; stroke-dashoffset: 200; animation: draw 1.4s 1s cubic-bezier(.6,.05,.15,1) forwards; }
  .navbar { transition: border-color .3s, background .3s; border-bottom:1px solid transparent; }
  .navbar.scrolled { border-color: var(--border); background: color-mix(in srgb, var(--bg) 82%, transparent); }
  @media (prefers-reduced-motion: reduce) {
    .ln i { transform:none; } .fade0,.fade1,.heroart { opacity:1; } .reveal { opacity:1; transform:none; } .vein { stroke-dashoffset:0; }
  }
</style>
</head>

<body class="overflow-x-hidden">

<!-- ambient botanical light -->
<div class="fixed inset-0 -z-[1] pointer-events-none overflow-hidden" aria-hidden="true">
  <span class="absolute -top-40 -left-24 w-[42rem] h-[42rem] rounded-full blur-[80px] opacity-25 animate-drift" style="background:var(--accent);mix-blend-mode:multiply;"></span>
  <span class="absolute -top-24 right-[-8rem] w-[34rem] h-[34rem] rounded-full blur-[80px] opacity-20 animate-floaty" style="background:var(--clay);mix-blend-mode:multiply;"></span>
</div>

<!-- NAV -->
<nav id="nav" class="navbar sticky top-0 z-[60] backdrop-blur-md">
  <div class="max-w-[1200px] mx-auto px-8 py-4 flex items-center justify-between">
    <a href="/" class="flex items-center gap-3 font-serif font-semibold text-[23px] tracking-[-.015em] no-underline text-ink">
      <span class="w-9 h-9 shrink-0">
        <svg viewBox="0 0 40 40" fill="none" width="36" height="36">
          <circle cx="20" cy="20" r="17" stroke="var(--accent)" stroke-width="1.5" stroke-dasharray="2 5" opacity=".5">
            <animateTransform attributeName="transform" type="rotate" from="0 20 20" to="360 20 20" dur="40s" repeatCount="indefinite"/>
          </circle>
          <path d="M20 8C15 12 12.5 15.5 12.5 20a7.5 7.5 0 0 0 15 0C27.5 15.5 25 12 20 8Z" fill="var(--accent)"/>
          <path d="M20 27V17" stroke="var(--surface-1)" stroke-width="1.7" stroke-linecap="round"/>
          <circle cx="20" cy="4" r="1.6" fill="var(--clay)"/>
        </svg>
      </span>
      <?= esc($brand) ?>
    </a>
    <div class="flex items-center gap-8">
      <a href="#platform" class="hidden md:inline text-[14.5px] text-ink-soft hover:text-ink transition">Platform</a>
      <a href="#how" class="hidden md:inline text-[14.5px] text-ink-soft hover:text-ink transition">How it works</a>
      <a href="#story" class="hidden md:inline text-[14.5px] text-ink-soft hover:text-ink transition">Why <?= esc($brand) ?></a>
      <a href="<?= site_url('login') ?>" class="group inline-flex items-center gap-2 bg-ink text-paper px-5 py-2.5 rounded-pill font-medium text-[14px] no-underline transition hover:-translate-y-0.5 hover:bg-sage-deep" style="color:var(--bg);">
        Log in
        <span class="transition-transform group-hover:translate-x-1"><?= icon('arrow-right', '', 16) ?></span>
      </a>
    </div>
  </div>
</nav>

<!-- HERO -->
<header class="relative pt-16 pb-10">
  <div class="wrap grid lg:grid-cols-[1.02fr_.98fr] gap-12 items-center">
    <div>
      <p class="fade0 eyebrow text-clay mb-6">People-first HR system</p>
      <h1 class="font-serif font-medium tracking-[-.028em] leading-[1.0] text-[clamp(44px,6.2vw,78px)]">
        <span class="ln"><i>HR that moves</i></span>
        <span class="ln" style="animation-delay:.12s"><i>as <em class="italic text-sage-deep">one</em>,</i></span>
        <span class="ln"><i>not one <span class="text-clay">chore</span>.</i></span>
      </h1>
      <p class="fade0 text-[18px] leading-[1.62] text-ink-soft max-w-[440px] mt-7 mb-8">
        <?= esc($brand) ?> brings your companies, branches, people, and payroll into one calm workspace built for teams who'd rather grow than chase paperwork.
      </p>
      <div class="fade1 flex flex-wrap items-center gap-3.5">
        <a href="<?= site_url('login') ?>" class="group inline-flex items-center gap-2.5 bg-sage text-white px-7 py-4 rounded-pill font-semibold text-[15px] no-underline transition-transform hover:-translate-y-0.5"
           style="box-shadow:0 8px 24px -6px rgba(79,100,70,.5);">
          Enter workspace
          <span class="transition-transform group-hover:translate-x-1"><?= icon('arrow-right', '', 18) ?></span>
        </a>
        <a href="#platform" class="inline-flex items-center gap-2.5 px-6 py-4 rounded-pill font-medium text-[15px] text-ink border-[1.5px] border-line transition hover:border-sage hover:text-sage-deep no-underline">
          <?= icon('play', '', 22) ?> See the platform
        </a>
      </div>
    </div>

    <!-- SIGNATURE: living org-tree product preview -->
    <div class="heroart relative" id="heroArt">
      <!-- floating chips -->
      <div class="absolute -top-3 right-6 z-20 hidden sm:flex items-center gap-2.5 bg-surface border border-line rounded-2xl px-3.5 py-2.5 shadow-lift animate-floaty" style="animation-delay:.2s">
        <span class="grid place-items-center w-8 h-8 rounded-[10px] bg-clay-soft text-clay"><?= icon('receipt', '', 17) ?></span>
        <div><p class="text-[10.5px] uppercase tracking-eyebrow text-ink-mute">Payroll</p><p class="font-serif text-[15px] font-medium leading-none mt-0.5">On schedule</p></div>
      </div>
      <div class="absolute bottom-2 -left-2 z-20 hidden sm:flex items-center gap-2.5 bg-surface border border-line rounded-2xl px-3.5 py-2.5 shadow-lift animate-floaty" style="animation-delay:1.4s">
        <span class="grid place-items-center w-8 h-8 rounded-[10px] bg-sage-soft text-sage-deep"><?= icon('clock', '', 17) ?></span>
        <div><p class="text-[10.5px] uppercase tracking-eyebrow text-ink-mute">Attendance</p><p class="font-serif text-[15px] font-medium leading-none mt-0.5">Live today</p></div>
      </div>

      <!-- workspace card -->
      <div class="relative bg-surface border border-line rounded-[26px] p-7 shadow-float overflow-hidden">
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center gap-2.5">
            <span class="grid place-items-center w-8 h-8 rounded-[10px] bg-sage text-white"><?= icon('leaf', '', 18) ?></span>
            <span class="font-serif font-semibold text-[16px]"><?= esc($brand) ?> workspace</span>
          </div>
          <span class="flex gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-line-strong"></span><span class="w-2.5 h-2.5 rounded-full bg-line-strong"></span><span class="w-2.5 h-2.5 rounded-full bg-sage"></span></span>
        </div>

        <!-- org tree that draws itself in -->
        <svg viewBox="0 0 360 210" fill="none" class="w-full">
          <!-- organic connective veins -->
          <path class="vein" d="M180 40 C180 66 96 60 96 96" stroke="var(--accent)" stroke-width="1.6" stroke-linecap="round"/>
          <path class="vein" d="M180 40 C180 66 264 60 264 96" stroke="var(--accent)" stroke-width="1.6" stroke-linecap="round" style="animation-delay:1.1s"/>
          <path class="vein" d="M96 120 C96 146 60 146 60 168" stroke="var(--accent)" stroke-width="1.4" stroke-linecap="round" style="animation-delay:1.3s"/>
          <path class="vein" d="M96 120 C96 146 132 146 132 168" stroke="var(--accent)" stroke-width="1.4" stroke-linecap="round" style="animation-delay:1.4s"/>
          <path class="vein" d="M264 120 C264 146 228 146 228 168" stroke="var(--accent)" stroke-width="1.4" stroke-linecap="round" style="animation-delay:1.4s"/>
          <path class="vein" d="M264 120 C264 146 300 146 300 168" stroke="var(--accent)" stroke-width="1.4" stroke-linecap="round" style="animation-delay:1.5s"/>

          <!-- root: company -->
          <g><rect x="150" y="18" width="60" height="26" rx="9" fill="var(--accent)"/><text x="180" y="35" text-anchor="middle" fill="#fff" font-family="Bricolage Grotesque" font-size="11" font-weight="600">Company</text></g>
          <!-- branches -->
          <g><rect x="60" y="96" width="72" height="26" rx="9" fill="var(--surface-2)" stroke="var(--border)"/><text x="96" y="113" text-anchor="middle" fill="var(--text)" font-family="Bricolage Grotesque" font-size="11" font-weight="500">HQ</text></g>
          <g><rect x="228" y="96" width="72" height="26" rx="9" fill="var(--surface-2)" stroke="var(--border)"/><text x="264" y="113" text-anchor="middle" fill="var(--text)" font-family="Bricolage Grotesque" font-size="11" font-weight="500">Branch</text></g>
          <!-- people leaves -->
          <?php $leaves = [[60,168],[132,168],[228,168],[300,168]]; foreach ($leaves as $i => $p): ?>
          <g transform="translate(<?= $p[0] ?>,<?= $p[1] ?>)"><circle r="15" fill="var(--surface-1)" stroke="var(--border)"/><path d="M0 -6C-3 -3 -4.5 -1 -4.5 1.6A4.5 4.5 0 0 0 4.5 1.6C4.5 -1 3 -3 0 -6Z" fill="var(--clay)" opacity=".9"/></g>
          <?php endforeach; ?>
        </svg>

        <div class="grid grid-cols-3 gap-3 mt-6">
          <div class="rounded-ctl bg-paper border border-line px-3 py-2.5"><p class="text-[10.5px] uppercase tracking-eyebrow text-ink-mute">People</p><p class="font-serif text-[22px] font-medium leading-none mt-1">201<span class="text-clay text-[.6em]">+</span></p></div>
          <div class="rounded-ctl bg-paper border border-line px-3 py-2.5"><p class="text-[10.5px] uppercase tracking-eyebrow text-ink-mute">Branches</p><p class="font-serif text-[22px] font-medium leading-none mt-1">4</p></div>
          <div class="rounded-ctl bg-paper border border-line px-3 py-2.5"><p class="text-[10.5px] uppercase tracking-eyebrow text-ink-mute">Companies</p><p class="font-serif text-[22px] font-medium leading-none mt-1">1</p></div>
        </div>
      </div>
    </div>
  </div>
</header>

<!-- MARQUEE -->
<div class="border-y border-line py-5 mt-9 overflow-hidden" style="background:color-mix(in srgb,var(--surface-1) 40%,transparent);">
  <div id="marq" class="flex whitespace-nowrap text-[13px] uppercase tracking-eyebrow text-ink-mute font-medium" style="animation:marq 30s linear infinite;"></div>
</div>

<!-- PLATFORM -->
<section id="platform" class="py-24">
  <div class="wrap">
    <div class="reveal max-w-[680px]">
      <p class="eyebrow text-clay mb-4">The platform</p>
      <h2 class="font-serif font-medium text-[clamp(32px,4.4vw,50px)] leading-[1.06] tracking-[-.022em]">Everything your people need, <em class="italic text-sage-deep">gathered in one place.</em></h2>
      <p class="text-[17px] text-ink-soft leading-relaxed mt-5 max-w-[560px]">No more scattered spreadsheets and lost employee files. <?= esc($brand) ?> keeps the whole organisation — every company, branch, and person — in one considered system.</p>
    </div>

    <div class="grid md:grid-cols-3 gap-5 mt-14">
      <?php
      $pillars = [
        ['icon' => 'building', 'h' => 'Company &amp; branches', 'p' => 'Register every company, map headquarters and branches, and hold all your registration details in one profile.'],
        ['icon' => 'sitemap',  'h' => 'People &amp; org structure', 'p' => 'Departments, positions, and reporting lines that mirror how your teams truly sit — ready for the org chart.'],
        ['icon' => 'receipt',  'h' => 'Payroll &amp; time', 'p' => 'Pay schedules, attendance, and leave that stay in step with each branch, so payday is never a scramble.'],
      ];
      foreach ($pillars as $p): ?>
      <div class="reveal group relative overflow-hidden bg-surface border border-line rounded-[22px] p-8 transition hover:-translate-y-1.5 hover:border-sage hover:shadow-float" data-glow>
        <span class="pointer-events-none absolute inset-0 opacity-0 group-hover:opacity-[.1] transition duration-500" style="background:radial-gradient(340px circle at var(--mx,50%) var(--my,0%),var(--accent),transparent 45%)"></span>
        <span class="relative grid place-items-center w-14 h-14 rounded-[17px] bg-paper-tint text-sage-deep mb-6 transition group-hover:bg-sage group-hover:text-white"><?= icon($p['icon'], '', 28) ?></span>
        <h3 class="relative font-serif text-[23px] font-semibold mb-2.5"><?= $p['h'] ?></h3>
        <p class="relative text-[14.5px] text-ink-soft leading-[1.62]"><?= $p['p'] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section id="how" class="py-24 border-y border-line" style="background:color-mix(in srgb,var(--surface-1) 45%,transparent);">
  <div class="wrap">
    <div class="reveal max-w-[640px]">
      <p class="eyebrow text-clay mb-4">How it works</p>
      <h2 class="font-serif font-medium text-[clamp(32px,4.4vw,50px)] leading-[1.06] tracking-[-.022em]">From empty to <em class="italic text-sage-deep">fully organised</em> in four moves.</h2>
    </div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-14 relative">
      <?php
      $steps = [
        ['n' => 'i',   'icon' => 'building',  'h' => 'Add your company', 'p' => 'Legal details, tax IDs, and statutory numbers — the full company profile.'],
        ['n' => 'ii',  'icon' => 'branch',    'h' => 'Map the branches',  'p' => 'Set HQ, add every location, and ' . esc($brand) . ' keeps a single headquarters honest.'],
        ['n' => 'iii', 'icon' => 'users',     'h' => 'Build the structure', 'p' => 'Departments and positions slot into place, ready for your people.'],
        ['n' => 'iv',  'icon' => 'checklist', 'h' => 'Run the day-to-day', 'p' => 'Attendance, leave, and payroll move together — calm and on time.'],
      ];
      foreach ($steps as $i => $s): ?>
      <div class="reveal relative bg-surface border border-line rounded-[20px] p-7 transition hover:-translate-y-1.5 hover:shadow-lift">
        <?php if ($i < 3): ?><span class="hidden lg:block absolute top-9 -right-3 z-[2] text-sage"><?= icon('arrow-right', '', 18) ?></span><?php endif; ?>
        <p class="font-serif italic text-[17px] text-clay mb-4"><?= $s['n'] ?>.</p>
        <span class="grid place-items-center w-11 h-11 rounded-[13px] bg-sage-soft text-sage-deep mb-4"><?= icon($s['icon'], '', 22) ?></span>
        <h4 class="font-serif text-[19px] font-semibold mb-2"><?= $s['h'] ?></h4>
        <p class="text-[13.5px] text-ink-soft leading-[1.55]"><?= $s['p'] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- STORY BAND -->
<section id="story" class="py-24">
  <div class="wrap">
    <div class="reveal relative overflow-hidden rounded-[36px] px-8 py-16 sm:px-14 text-white" style="background:var(--accent-deep);">
      <svg class="absolute -right-14 -bottom-20 w-[360px] opacity-50" viewBox="0 0 360 360" fill="none" aria-hidden="true">
        <circle cx="180" cy="180" r="120" stroke="var(--accent)" stroke-width="1" stroke-dasharray="2 6" opacity=".6"/>
        <circle cx="180" cy="180" r="160" stroke="var(--accent)" stroke-width="1" stroke-dasharray="2 6" opacity=".35"/>
        <path d="M180 250c-22 16-32 30-32 48a32 32 0 0 0 64 0c0-18-10-32-32-48Z" fill="var(--accent)" opacity=".5"/>
        <circle cx="300" cy="180" r="4" fill="var(--accent)"/><circle cx="180" cy="60" r="3" fill="var(--gold)"/>
      </svg>
      <p class="relative font-serif text-[clamp(28px,3.6vw,42px)] font-medium leading-[1.12] max-w-[680px]">
        Built by people who've <em class="italic" style="color:var(--accent);">felt the Monday-morning payroll panic</em> and decided it didn't have to be that way.
      </p>
      <div class="relative grid grid-cols-2 lg:grid-cols-4 gap-8 mt-12">
        <?php
        $band = [
          ['t' => 1,   'u' => '',  'l' => 'Workspace for every company you run'],
          ['t' => 4,   'u' => '',  'l' => 'Branches mapped in the current demo'],
          ['t' => 201, 'u' => '+', 'l' => 'Employee files, kept in order'],
          ['t' => 1,   'u' => '',  'l' => 'Calm, considered place for it all'],
        ];
        foreach ($band as $b): ?>
        <div>
          <div class="font-serif text-[clamp(40px,5vw,58px)] font-semibold leading-none"><span data-target="<?= $b['t'] ?>">0</span><span style="color:var(--accent);"><?= $b['u'] ?></span></div>
          <p class="text-[13.5px] mt-2.5" style="color:color-mix(in srgb,#fff 72%,transparent);"><?= $b['l'] ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- FINAL CTA -->
<section class="text-center pt-8 pb-28">
  <div class="wrap reveal">
    <svg class="w-9 h-9 mx-auto mb-6 text-clay" style="animation:twinkle 3s ease-in-out infinite" viewBox="0 0 34 34" fill="none"><path d="M17 2l3 11 11 3-11 3-3 11-3-11-11-3 11-3 3-11Z" fill="currentColor"/></svg>
    <h2 class="font-serif font-medium text-[clamp(32px,4.4vw,50px)] leading-[1.06] tracking-[-.022em]">Gather your team.<br><em class="italic text-sage-deep">Rise with <?= esc($brand) ?>.</em></h2>
    <p class="text-[17px] text-ink-soft leading-relaxed max-w-[520px] mx-auto mt-5 mb-9">Sign in to the workspace and watch your whole organisation come together.</p>
    <a href="<?= site_url('login') ?>" class="group inline-flex items-center gap-2.5 bg-sage text-white px-7 py-4 rounded-pill font-semibold text-[15px] no-underline transition-transform hover:-translate-y-0.5" style="box-shadow:0 8px 24px -6px rgba(79,100,70,.5);">
      Enter workspace <span class="transition-transform group-hover:translate-x-1"><?= icon('arrow-right', '', 18) ?></span>
    </a>
  </div>
</section>

<!-- FOOTER -->
<footer class="border-t border-line py-14">
  <div class="wrap">
    <div class="flex flex-wrap justify-between items-start gap-10">
      <div class="max-w-[300px]">
        <a href="/" class="flex items-center gap-3 font-serif font-semibold text-[20px] no-underline text-ink mb-4">
          <span class="w-8 h-8"><?= icon('leaf', 'text-sage', 30) ?></span><?= esc($brand) ?>
        </a>
        <p class="text-[14px] text-ink-soft leading-relaxed">People-first HR, built for teams who'd rather grow than chase paperwork.</p>
      </div>
      <div class="flex flex-wrap gap-16">
        <div>
          <h5 class="eyebrow mb-4">Platform</h5>
          <a href="#platform" class="block text-[14px] text-ink-soft mb-2.5 hover:text-sage-deep transition">Company settings</a>
          <a href="#platform" class="block text-[14px] text-ink-soft mb-2.5 hover:text-sage-deep transition">People &amp; org</a>
          <a href="#platform" class="block text-[14px] text-ink-soft mb-2.5 hover:text-sage-deep transition">Payroll &amp; time</a>
          <a href="#how" class="block text-[14px] text-ink-soft mb-2.5 hover:text-sage-deep transition">How it works</a>
        </div>
        <div>
          <h5 class="eyebrow mb-4">Workspace</h5>
          <a href="<?= site_url('login') ?>" class="block text-[14px] text-ink-soft mb-2.5 hover:text-sage-deep transition">Log in</a>
          <a href="#story" class="block text-[14px] text-ink-soft mb-2.5 hover:text-sage-deep transition">Why <?= esc($brand) ?></a>
        </div>
      </div>
    </div>
    <div class="mt-12 pt-6 border-t border-line flex flex-wrap justify-between gap-2.5 text-[13px] text-ink-mute">
      <span>© <?= date('Y') ?> <?= esc($brand) ?> · HR that moves as one</span>
      <span class="font-serif italic"><?= esc(strtolower($brand)) ?> · rise, together</span>
    </div>
  </div>
</footer>

<script>
  // sticky nav border on scroll
  var nav = document.getElementById('nav');
  addEventListener('scroll', function () { nav.classList.toggle('scrolled', scrollY > 12); });

  // scroll reveal
  var io = new IntersectionObserver(function (es) {
    es.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
  }, { threshold: .14 });
  document.querySelectorAll('.reveal').forEach(function (el, i) { el.style.transitionDelay = (i % 4 * 0.06) + 's'; io.observe(el); });

  // marquee (duplicated for a seamless loop)
  (function () {
    var items = ['One workspace','All your companies','Branches & HQ mapped','Employee files in order','Departments structured','Payroll on schedule','Leave tracked','Org chart ready'];
    var html = '';
    for (var k = 0; k < 2; k++) items.forEach(function (t) {
      html += '<span class="inline-flex items-center pr-14"><span style="color:var(--accent-deep)">' + t + '</span><span class="ml-3 w-[5px] h-[5px] rounded-full" style="background:var(--clay);opacity:.6"></span></span>';
    });
    document.getElementById('marq').innerHTML = html;
  })();

  // count-up in the story band
  var band = document.querySelector('[data-target]') && document.querySelector('#story .reveal');
  if (band) {
    new IntersectionObserver(function (es, obs) {
      es.forEach(function (e) {
        if (!e.isIntersecting) return;
        document.querySelectorAll('span[data-target]').forEach(function (s) {
          var t = +s.dataset.target, d = 1500, st = performance.now();
          (function tick(now) { var p = Math.min((now - st) / d, 1), ev = 1 - Math.pow(1 - p, 3); s.textContent = Math.round(t * ev); if (p < 1) requestAnimationFrame(tick); })(performance.now());
        });
        obs.disconnect();
      });
    }, { threshold: .4 }).observe(band);
  }

  // feature-card cursor glow
  document.querySelectorAll('[data-glow]').forEach(function (p) {
    p.addEventListener('mousemove', function (e) { var r = p.getBoundingClientRect(); p.style.setProperty('--mx', (e.clientX - r.left) + 'px'); p.style.setProperty('--my', (e.clientY - r.top) + 'px'); });
  });
</script>
</body>
</html>
