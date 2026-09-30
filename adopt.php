<?php
require_once __DIR__ . '/inc/functions.php';

$petSlug = isset($_GET['pet']) ? clean($_GET['pet'], 60) : '';
$pet = $petSlug !== '' ? one('SELECT * FROM pets WHERE slug = ?', [$petSlug]) : null;

$sql = "SELECT a.*, pt.title ptitle, pt.slug pslug FROM animals a
        JOIN pets pt ON pt.id = a.pet_id WHERE a.active = 1";
$par = [];
if ($pet) { $sql .= ' AND a.pet_id = ?'; $par[] = $pet['id']; }
$sql .= " ORDER BY CASE a.status WHEN 'ready' THEN 0 WHEN 'reserved' THEN 1 ELSE 2 END, a.id";
$anims = all($sql, $par);

$pets = all('SELECT p.*, COUNT(a.id) n FROM pets p
             LEFT JOIN animals a ON a.pet_id = p.id AND a.active = 1
             WHERE p.adopt = 1 GROUP BY p.id ORDER BY p.sort_order');

$NAV = 'adopt';
$PAGE_TITLE = 'سرپرستی حیوان — ' . $CFG['site_name'];
$PAGE_DESC  = 'حیوون‌های آماده سرپرستی بانی‌پت، با راهنمایی کامل قبل و بعد از تحویل.';
require __DIR__ . '/inc/header.php';
?>

<section class="pagehead">
  <div class="dust" aria-hidden="true"></div>
  <span class="blob f2" style="width:140px;height:140px;top:-30px;right:10%;background:#fff;opacity:.5"></span>
  <div class="wrap">
    <div class="crumb"><a href="<?= url('index.php') ?>">خانه</a> ← سرپرستی<?= $pet ? ' ← ' . e($pet['title']) : '' ?></div>
    <h1 class="h2">سرپرستی حیوان</h1>
    <p class="lead" style="max-width:640px">
      ما حیوون رو «نمی‌فروشیم» و تحویل هم عجله‌ای انجام نمی‌شه. اول آشنا می‌شی، بعد درباره خونه و
      برنامه‌ت حرف می‌زنیم، و اگه جور بود سرپرستی انجام می‌شه.
    </p>
  </div>
</section>

<!-- مراحل -->
<section class="steps">
  <div class="wrap">
    <div class="stepgrid">
      <div class="step reveal"><b>۱</b><h3>آشنا شدن</h3><p>پرونده حیوون رو می‌خونی و درخواست می‌دی</p></div>
      <div class="step reveal"><b>۲</b><h3>گفت‌وگو</h3><p>یه تماس کوتاه درباره فضای خونه و وقتت</p></div>
      <div class="step reveal"><b>۳</b><h3>آماده‌سازی</h3><p>لوازم لازم رو با هم لیست می‌کنیم</p></div>
      <div class="step reveal"><b>۴</b><h3>تحویل حضوری</h3><p>و پیگیری روز دوم و روز هفتم</p></div>
    </div>
  </div>
</section>

<div class="wrap">
  <div class="layout">
    <aside class="side">
      <h3>خانواده‌ها</h3>
      <ul>
        <li><a href="<?= url('adopt.php') ?>"<?= active_if(!$pet) ?>>همه</a></li>
        <?php foreach ($pets as $p): ?>
          <li><a href="<?= url('adopt.php?pet=' . urlencode($p['slug'])) ?>"<?= active_if($pet && $pet['id'] == $p['id']) ?>>
            <span><?= e($p['emoji']) ?> <?= e($p['title']) ?></span><small><?= fa_num($p['n']) ?></small></a></li>
        <?php endforeach; ?>
      </ul>
      <div class="note warn" style="margin-top:14px;font-size:12.5px">
        حیوون زنده با پست ارسال نمی‌شه. تحویل فقط حضوریه.
      </div>
    </aside>

    <div>
      <?php if (!$anims): ?>
        <div class="empty"><div class="big">🐾</div>
          <p>الان چیزی برای سرپرستی نداریم. شماره‌ت رو بذار تا خبرت کنیم.</p>
          <a class="btn" href="<?= url('index.php#join') ?>">اطلاع بده</a></div>
      <?php else: ?>
        <div class="prodgrid three">
          <?php foreach ($anims as $a): ?>
            <div class="card reveal<?= $a['status'] === 'adopted' ? ' dim' : '' ?>">
              <span class="tag <?= $a['status'] === 'ready' ? '' : 'blue' ?>"><?= e(status_label($a['status'])) ?></span>
              <a href="<?= url('animal.php?slug=' . urlencode($a['slug'])) ?>">
                <span class="ph <?= e($a['tone']) ?>"><?= e($a['name']) ?></span>
                <h3><?= e($a['name']) ?> — <?= e($a['breed']) ?></h3>
              </a>
              <div class="meta small"><?= e($a['age']) ?> · <?= e($a['sex']) ?> · <?= e($a['ptitle']) ?></div>
              <div class="row">
                <span class="worthlabel">ارزش سرپرستی</span>
                <span class="price"><?= price_label($a['worth']) ?></span>
              </div>
              <a class="add" href="<?= url('animal.php?slug=' . urlencode($a['slug'])) ?>">آشنا شو</a>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>
