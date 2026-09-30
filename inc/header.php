<?php
require_once __DIR__ . '/functions.php';
$U = current_user();
$PAGE_TITLE = isset($PAGE_TITLE) ? $PAGE_TITLE : $CFG['site_name'] . ' — ' . $CFG['site_slogan'];
$PAGE_DESC  = isset($PAGE_DESC) ? $PAGE_DESC
  : 'فروشگاه آنلاین حیوانات خانگی و لوازم نگهداری، با مشاوره قبل و بعد از خرید.';
$NAV = isset($NAV) ? $NAV : '';
$navPets = all('SELECT slug, title, emoji FROM pets ORDER BY sort_order');
$navCats = all('SELECT slug, title FROM categories ORDER BY sort_order LIMIT 6');
$F = flash();
?><!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($PAGE_TITLE) ?></title>
<meta name="description" content="<?= e($PAGE_DESC) ?>">
<meta name="theme-color" content="#FDF0F5">
<?php $CANON = isset($CANON) ? $CANON : url(basename($_SERVER['SCRIPT_NAME'])
      . (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] !== ''
         ? '?' . $_SERVER['QUERY_STRING'] : '')); ?>
<link rel="canonical" href="<?= e($CANON) ?>">
<meta property="og:url" content="<?= e($CANON) ?>">
<meta property="og:site_name" content="<?= e($CFG['site_name']) ?>">
<meta property="og:image" content="<?= url('assets/img/rabbit.png') ?>">
<meta name="twitter:card" content="summary_large_image">
<meta property="og:title" content="<?= e($PAGE_TITLE) ?>">
<meta property="og:description" content="<?= e($PAGE_DESC) ?>">
<meta property="og:type" content="website">
<meta property="og:locale" content="fa_IR">
<link rel="icon" href="<?= url('assets/img/rabbit.webp') ?>">
<link rel="preload" href="<?= url('assets/fonts/Vazirmatn-Regular.woff2') ?>" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
<?php if (!empty($JSONLD)): ?>
<script type="application/ld+json"><?= json_encode($JSONLD, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<?php endif; ?>
</head>
<body>
<div class="grain" aria-hidden="true"></div>

<header class="topbar">
  <div class="wrap">
    <a class="logo" href="<?= url('index.php') ?>">
      <svg width="42" height="42" viewBox="0 0 48 48" aria-hidden="true">
        <ellipse cx="17" cy="14" rx="5" ry="10" fill="#F9B8D0"/>
        <ellipse cx="31" cy="14" rx="5" ry="10" fill="#AEDCF2"/>
        <circle cx="24" cy="30" r="13" fill="#fff" stroke="#F9B8D0" stroke-width="2"/>
        <circle cx="19.5" cy="28" r="1.8" fill="#4A4855"/>
        <circle cx="28.5" cy="28" r="1.8" fill="#4A4855"/>
        <path d="M24 32.5c-1.6 0-2.8 1-2.8 2.2 0 1.4 1.5 2.3 2.8 2.3s2.8-.9 2.8-2.3c0-1.2-1.2-2.2-2.8-2.2z" fill="#EC6BA0"/>
      </svg>
      <span class="txt">
        <b><?= e($CFG['site_name']) ?></b>
        <small><?= e($CFG['site_slogan']) ?></small>
      </span>
    </a>

    <form class="search" method="get" action="<?= url('shop.php') ?>" role="search">
      <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#8B889A" stroke-width="2" aria-hidden="true">
        <circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5" stroke-linecap="round"/>
      </svg>
      <label class="sr" for="q">جستجو</label>
      <input id="q" name="q" type="search" placeholder="دنبال چی می‌گردی؟"
             value="<?= isset($_GET['q']) ? e($_GET['q']) : '' ?>" maxlength="60">
    </form>

    <nav class="icons" aria-label="حساب و سبد">
      <a class="iconbtn" href="<?= url($U ? 'account.php' : 'login.php') ?>">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4A4855" stroke-width="1.7" aria-hidden="true">
          <circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5" stroke-linecap="round"/>
        </svg>
        <span><?= $U ? e($U['name']) : 'ورود / ثبت‌نام' ?></span>
      </a>
      <a class="iconbtn" href="<?= url('cart.php') ?>" aria-label="سبد خرید">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="#4A4855" stroke-width="1.7" aria-hidden="true">
          <path d="M4 5h2l2.2 10.5h9L20 8H7" stroke-linecap="round" stroke-linejoin="round"/>
          <circle cx="10" cy="19.5" r="1.4"/><circle cx="17" cy="19.5" r="1.4"/>
        </svg>
        <span>سبد</span>
        <?php if (cart_count()): ?><b class="badge"><?= fa_num(cart_count()) ?></b><?php endif; ?>
      </a>
    </nav>
  </div>
</header>

<nav class="nav" aria-label="منوی اصلی">
  <div class="wrap">
    <a href="<?= url('index.php') ?>"<?= active_if($NAV === 'home') ?>>خانه</a>
    <a href="<?= url('adopt.php') ?>"<?= active_if($NAV === 'adopt') ?>>سرپرستی حیوان</a>
    <a href="<?= url('shop.php') ?>"<?= active_if($NAV === 'shop') ?>>فروشگاه</a>
    <a href="<?= url('rahnama.php?pet=khargoosh') ?>"<?= active_if($NAV === 'guide') ?>>راهنمای نگهداری</a>
    <a href="<?= url('blog.php') ?>"<?= active_if($NAV === 'blog') ?>>مجله</a>
    <a href="<?= url('about.php') ?>"<?= active_if($NAV === 'about') ?>>درباره ما</a>
    <a href="<?= url('faq.php') ?>"<?= active_if($NAV === 'faq') ?>>پرسش‌های پرتکرار</a>
    <a href="<?= url('contact.php') ?>"<?= active_if($NAV === 'contact') ?>>تماس</a>
  </div>
</nav>

<div class="petbar" aria-label="خانواده حیوون‌ها">
  <div class="wrap">
    <?php foreach ($navPets as $np): ?>
      <a href="<?= url('pet.php?slug=' . urlencode($np['slug'])) ?>"
         <?= active_if(isset($PETNAV) && $PETNAV === $np['slug']) ?>>
        <span><?= e($np['emoji']) ?></span><?= e($np['title']) ?>
      </a>
    <?php endforeach; ?>
  </div>
</div>

<svg class="wave navwave" viewBox="0 0 1440 44" preserveAspectRatio="none" aria-hidden="true" style="height:30px">
  <path fill="currentColor" d="M0 0h1440v16c-180 26-360 26-540 12S540 4 360 10 90 30 0 40z"/>
</svg>

<main>
<?php if ($F): ?>
  <div class="wrap" style="padding-top:16px">
    <div class="note <?= e($F['type']) ?>"><?= e($F['msg']) ?></div>
  </div>
<?php endif; ?>
