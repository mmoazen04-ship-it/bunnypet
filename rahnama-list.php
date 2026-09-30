<?php
/* فهرست راهنمای نگهداری — کارت‌های هم‌اندازه، بدون بی‌نظمی */
require_once __DIR__ . '/inc/functions.php';

$gslug = isset($_GET['pet']) ? clean($_GET['pet'], 60) : 'khargoosh';
$pet = one('SELECT * FROM pets WHERE slug = ?', [$gslug]);
$secs = all('SELECT * FROM guide_sections WHERE guide = ? AND active = 1 ORDER BY num', [$gslug]);
if (!$pet || !$secs) { http_response_code(404); }

$NAV = 'guide';
$PETNAV = $gslug;
$PAGE_TITLE = 'راهنمای کامل نگهداری ' . ($pet ? $pet['title'] : '') . ' — ' . $CFG['site_name'];
$PAGE_DESC  = 'از تغذیه و یونجه تا دما، دستشویی، بازی و نشونه‌های بیماری؛ '
            . fa_num(count($secs)) . ' راهنمای کوتاه و کاربردی.';
if ($pet && $secs) {
  $CANON = url('rahnama.php?pet=' . $gslug);
  $list = [];
  foreach ($secs as $i => $s) {
    $list[] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $s['title'],
               'url' => url('rahnama.php?pet=' . $gslug . '&b=' . $s['slug'])];
  }
  $JSONLD = ['@context' => 'https://schema.org', '@type' => 'ItemList',
             'name' => 'راهنمای نگهداری ' . $pet['title'], 'itemListElement' => $list];
}
require __DIR__ . '/inc/header.php';
?>

<?php if (!$pet || !$secs): ?>
  <div class="empty"><div class="big">📘</div>
    <h1 class="h2">این راهنما هنوز آماده نیست</h1>
    <a class="btn" href="<?= url('blog.php') ?>">مجله بانی‌پت</a></div>
<?php else: ?>

<section class="pagehead guidehead">
  <div class="dust" aria-hidden="true"></div>
  <span class="blob f1" style="width:170px;height:170px;top:-40px;left:6%;background:#fff;opacity:.5"></span>
  <span class="blob f2" style="width:110px;height:110px;bottom:8%;right:9%;background:var(--pink-mid);opacity:.35"></span>
  <div class="wrap">
    <div class="crumb">
      <a href="<?= url('index.php') ?>">خانه</a> ←
      <a href="<?= url('pet.php?slug=' . urlencode($gslug)) ?>"><?= e($pet['title']) ?></a> ← راهنمای نگهداری
    </div>
    <span class="kicker">راهنمای کامل</span>
    <h1 class="h1" style="font-size:clamp(26px,5vw,44px)">نگهداری <?= e($pet['title']) ?>، قدم به قدم</h1>
    <p class="lead" style="max-width:600px">
      <?= fa_num(count($secs)) ?> راهنمای کوتاه، هر کدوم یه صفحه جدا.
      از همون اولی شروع کن یا مستقیم برو سراغ چیزی که لازم داری.
    </p>
    <div class="btns">
      <a class="btn" href="<?= url('rahnama.php?pet=' . urlencode($gslug) . '&b=' . $secs[0]['slug']) ?>">
        از اول شروع کن</a>
      <a class="btn ghost" href="<?= url('shop.php?pet=' . urlencode($gslug)) ?>">
        لوازم <?= e($pet['title']) ?></a>
    </div>
  </div>
  <svg class="wave" viewBox="0 0 1440 70" preserveAspectRatio="none" aria-hidden="true">
    <path fill="#ffffff" d="M0 30c180-30 360-34 540-18s360 44 540 30 240-30 360-40v68H0z"/>
  </svg>
</section>

<div class="wrap" style="padding-bottom:54px">
  <div class="guidegrid">
    <?php foreach ($secs as $s): ?>
      <a class="gcard reveal" href="<?= url('rahnama.php?pet=' . urlencode($gslug) . '&b=' . urlencode($s['slug'])) ?>">
        <?php if ($s['img'] && is_file(__DIR__ . '/assets/img/guide/' . $s['img'])): ?>
          <img class="gcover" src="<?= url('assets/img/guide/' . rawurlencode($s['img'])) ?>"
               alt="<?= e($s['title']) ?>" loading="lazy">
        <?php else: ?>
          <span class="gcover ph <?= e($s['tone']) ?>"><span class="gicon"><?= e($s['icon']) ?></span></span>
        <?php endif; ?>
        <span class="gnum"><?= fa_num($s['num']) ?></span>
        <h2><?= e($s['title']) ?></h2>
        <p><?= e(excerpt($s['body'], 78)) ?></p>
        <span class="go">بخون ←</span>
      </a>
    <?php endforeach; ?>
  </div>

  <div class="summary" style="position:static;margin-top:26px;text-align:center">
    <h2 class="h3">سؤالی برات مونده؟</h2>
    <p class="lead">یه تماس کوتاه می‌گیریم و راهنماییت می‌کنیم. رایگانه.</p>
    <div class="btns" style="justify-content:center">
      <a class="btn" href="<?= url('contact.php') ?>">بپرس از ما</a>
      <a class="btn ghost" href="<?= url('adopt.php?pet=' . urlencode($gslug)) ?>">
        <?= e($pet['title']) ?>های آماده سرپرستی</a>
    </div>
  </div>
</div>

<?php endif; ?>
<?php require __DIR__ . '/inc/footer.php'; ?>
