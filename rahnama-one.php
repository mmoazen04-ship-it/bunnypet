<?php
/* یک بخش از راهنمای نگهداری — چیدمان یکدست و ساده */
require_once __DIR__ . '/inc/functions.php';

$gslug = isset($_GET['pet']) ? clean($_GET['pet'], 60) : 'khargoosh';
$bslug = isset($_GET['b']) ? clean($_GET['b'], 90) : '';
$pet = one('SELECT * FROM pets WHERE slug = ?', [$gslug]);
$s = one('SELECT * FROM guide_sections WHERE guide = ? AND slug = ? AND active = 1',
         [$gslug, $bslug]);
if (!$pet || !$s) { http_response_code(404); }

$prev = $s ? one('SELECT slug, title FROM guide_sections WHERE guide = ? AND active = 1
                  AND num < ? ORDER BY num DESC', [$gslug, $s['num']]) : null;
$next = $s ? one('SELECT slug, title FROM guide_sections WHERE guide = ? AND active = 1
                  AND num > ? ORDER BY num ASC', [$gslug, $s['num']]) : null;
$total = one('SELECT COUNT(*) c FROM guide_sections WHERE guide = ? AND active = 1', [$gslug]);

function video_embed($u) {
  $u = trim((string)$u);
  if ($u === '') return null;
  if (preg_match('~aparat\.com/v/([A-Za-z0-9]+)~', $u, $m)
      || preg_match('~aparat\.com/video/video/embed/videohash/([A-Za-z0-9]+)~', $u, $m)) {
    return ['iframe', 'https://www.aparat.com/video/video/embed/videohash/' . $m[1] . '/vt/frame'];
  }
  if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([A-Za-z0-9_-]{6,})~', $u, $m)) {
    return ['iframe', 'https://www.youtube-nocookie.com/embed/' . $m[1]];
  }
  if (preg_match('~\.(mp4|webm|ogg)(\?.*)?$~i', $u)) { return ['file', $u]; }
  return ['link', $u];
}
$vid = $s ? video_embed($s['video']) : null;

$NAV = 'guide';
$PETNAV = $gslug;
$PAGE_TITLE = ($s ? $s['title'] : 'پیدا نشد') . ' — راهنمای نگهداری '
            . ($pet ? $pet['title'] : '') . ' — ' . $CFG['site_name'];
if ($s) {
  $PAGE_DESC = excerpt($s['body'], 150);
  $CANON = url('rahnama.php?pet=' . $gslug . '&b=' . $s['slug']);
  $JSONLD = [
    '@context' => 'https://schema.org', '@type' => 'Article',
    'headline' => $s['title'], 'description' => $PAGE_DESC, 'inLanguage' => 'fa-IR',
    'isPartOf' => ['@type' => 'CreativeWork',
                   'name' => 'راهنمای نگهداری ' . $pet['title'],
                   'url' => url('rahnama.php?pet=' . $gslug)],
    'publisher' => ['@type' => 'Organization', 'name' => $CFG['site_name']],
    'mainEntityOfPage' => $CANON,
  ];
}
require __DIR__ . '/inc/header.php';
?>

<?php if (!$s): ?>
  <div class="empty"><div class="big">📘</div>
    <h1 class="h2">این بخش پیدا نشد</h1>
    <a class="btn" href="<?= url('rahnama.php?pet=' . urlencode($gslug)) ?>">فهرست راهنما</a></div>
<?php else: ?>

<section class="pagehead" style="padding:26px 0 20px">
  <div class="dust" aria-hidden="true"></div>
  <div class="wrap">
    <div class="crumb">
      <a href="<?= url('index.php') ?>">خانه</a> ←
      <a href="<?= url('pet.php?slug=' . urlencode($gslug)) ?>"><?= e($pet['title']) ?></a> ←
      <a href="<?= url('rahnama.php?pet=' . urlencode($gslug)) ?>">راهنمای نگهداری</a> ←
      <?= e($s['title']) ?>
    </div>
  </div>
</section>

<article class="gone">
  <div class="gonehead">
    <span class="gstep">بخش <?= fa_num($s['num']) ?> از <?= fa_num($total['c']) ?></span>
    <h1><?= e($s['title']) ?></h1>
  </div>

  <?php if ($s['img'] && is_file(__DIR__ . '/assets/img/guide/' . $s['img'])): ?>
    <img class="gonecover" src="<?= url('assets/img/guide/' . rawurlencode($s['img'])) ?>"
         alt="<?= e($s['title']) ?>">
  <?php else: ?>
    <div class="gonecover ph <?= e($s['tone']) ?>" aria-hidden="true">
      <span class="gicon"><?= e($s['icon']) ?></span>
      <small>جای عکس این بخش</small>
    </div>
  <?php endif; ?>

  <div class="gonebody">
    <p><?= e($s['body']) ?></p>

    <?php if ($s['do_tip'] || $s['dont_tip'] || $s['danger_tip']): ?>
      <div class="tips">
        <?php if ($s['do_tip']): ?>
          <div class="tip ok"><b>انجام بده</b><span><?= e($s['do_tip']) ?></span></div>
        <?php endif; ?>
        <?php if ($s['dont_tip']): ?>
          <div class="tip no"><b>انجام نده</b><span><?= e($s['dont_tip']) ?></span></div>
        <?php endif; ?>
        <?php if ($s['danger_tip']): ?>
          <div class="tip danger"><b>خطر</b><span><?= e($s['danger_tip']) ?></span></div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if ($vid): ?>
      <?php if ($vid[0] === 'iframe'): ?>
        <div class="gvideo">
          <iframe src="<?= e($vid[1]) ?>" title="<?= e($s['title']) ?>" allowfullscreen
                  loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
        </div>
      <?php elseif ($vid[0] === 'file'): ?>
        <div class="gvideo"><video src="<?= e($vid[1]) ?>" controls preload="none"></video></div>
      <?php else: ?>
        <a class="btn ghost mini" href="<?= e($vid[1]) ?>" target="_blank" rel="noopener">دیدن ویدیو</a>
      <?php endif; ?>
    <?php endif; ?>
  </div>

  <nav class="gnav">
    <?php if ($prev): ?>
      <a class="gnavbtn" href="<?= url('rahnama.php?pet=' . urlencode($gslug) . '&b=' . urlencode($prev['slug'])) ?>">
        <small>بخش قبلی</small><b><?= e($prev['title']) ?></b></a>
    <?php else: ?><span></span><?php endif; ?>

    <?php if ($next): ?>
      <a class="gnavbtn next" href="<?= url('rahnama.php?pet=' . urlencode($gslug) . '&b=' . urlencode($next['slug'])) ?>">
        <small>بخش بعدی</small><b><?= e($next['title']) ?></b></a>
    <?php else: ?><span></span><?php endif; ?>
  </nav>

  <div class="summary" style="position:static;text-align:center;margin-top:24px">
    <a class="btn ghost" href="<?= url('rahnama.php?pet=' . urlencode($gslug)) ?>">
      فهرست کامل راهنما</a>
    <a class="btn" href="<?= url('contact.php') ?>">سؤالت رو بپرس</a>
  </div>
</article>

<?php endif; ?>
<?php require __DIR__ . '/inc/footer.php'; ?>
