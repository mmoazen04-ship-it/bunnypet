<?php
/* لایه ۱ — صفحه خانواده حیوون: حیوون‌های آماده سرپرستی + لوازم + مقاله‌ها */
require_once __DIR__ . '/inc/functions.php';

$slug = isset($_GET['slug']) ? clean($_GET['slug'], 60) : '';
$pet  = one('SELECT * FROM pets WHERE slug = ?', [$slug]);
if (!$pet) { http_response_code(404); }

$NAV = '';
$PETNAV = $slug;
$PAGE_TITLE = ($pet ? $pet['title'] : 'پیدا نشد') . ' — ' . $CFG['site_name'];
$PAGE_DESC  = $pet ? $pet['subtitle'] : '';
require __DIR__ . '/inc/header.php';
?>

<?php if (!$pet): ?>
  <div class="empty"><div class="big">🐾</div>
    <h1 class="h2">این صفحه پیدا نشد</h1>
    <a class="btn" href="<?= url('index.php') ?>">برگرد به خانه</a></div>
<?php else:
  $anims = all("SELECT * FROM animals WHERE pet_id = ? AND active = 1
                ORDER BY CASE status WHEN 'ready' THEN 0 WHEN 'reserved' THEN 1 ELSE 2 END, id",
               [$pet['id']]);
  $cats = all('SELECT c.*, COUNT(p.id) n FROM categories c
               JOIN products p ON p.cat_id = c.id AND p.pet_id = ? AND p.active = 1
               GROUP BY c.id ORDER BY c.sort_order', [$pet['id']]);
  $prods = all('SELECT * FROM products WHERE pet_id = ? AND active = 1 ORDER BY id LIMIT 8',
               [$pet['id']]);
  $posts = all('SELECT * FROM articles WHERE pet_id = ? AND active = 1 ORDER BY created_at DESC LIMIT 3',
               [$pet['id']]);
?>

<section class="pagehead">
  <div class="dust" aria-hidden="true"></div>
  <span class="blob f1" style="width:160px;height:160px;top:-40px;left:7%;background:#fff;opacity:.5"></span>
  <div class="wrap">
    <div class="crumb"><a href="<?= url('index.php') ?>">خانه</a> ← <?= e($pet['title']) ?></div>
    <h1 class="h2"><?= e($pet['emoji']) ?> <?= e($pet['title']) ?></h1>
    <p class="lead" style="max-width:620px"><?= e($pet['intro']) ?></p>
    <div class="btns">
      <?php if ($pet['adopt']): ?>
        <a class="btn" href="#adopt">آماده سرپرستی</a>
      <?php endif; ?>
      <a class="btn ghost" href="#lavazem">لوازم <?= e($pet['title']) ?></a>
    </div>
  </div>
</section>

<?php if ($pet['adopt']): ?>
<section class="mag" id="adopt">
  <div class="wrap">
    <div class="sechead reveal">
      <h2 class="h2"><?= e($pet['title']) ?>های آماده سرپرستی</h2>
      <p>ارزش سرپرستی شامل واکسن و معاینه انجام‌شده تا امروزه</p>
    </div>
    <?php if (!$anims): ?>
      <div class="empty" style="padding:30px">
        <p>الان <?= e($pet['title']) ?> آماده‌ای نداریم. شماره‌ت رو بذار تا تا رسید خبرت کنیم.</p>
        <a class="btn" href="<?= url('index.php#join') ?>">اطلاع بده</a>
      </div>
    <?php else: ?>
      <div class="prodgrid">
        <?php foreach ($anims as $a): ?>
          <div class="card reveal">
            <span class="tag <?= $a['status'] === 'ready' ? '' : 'blue' ?>">
              <?= e(status_label($a['status'])) ?></span>
            <a href="<?= url('animal.php?slug=' . urlencode($a['slug'])) ?>">
              <span class="ph <?= e($a['tone']) ?>"><?= e($a['name']) ?></span>
              <h3><?= e($a['name']) ?> — <?= e($a['breed']) ?></h3>
            </a>
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
</section>
<?php endif; ?>

<section class="products" id="lavazem">
  <div class="dust" aria-hidden="true"></div>
  <svg class="wave" viewBox="0 0 1440 70" preserveAspectRatio="none" aria-hidden="true"
       style="margin-top:-1px;position:relative;z-index:2">
    <path fill="#ffffff" d="M0 0h1440v18c-220 30-460 26-720 6S240 6 0 30z"/>
  </svg>
  <div class="wrap">
    <div class="sechead reveal">
      <h2 class="h2">لوازم <?= e($pet['title']) ?></h2>
      <p>دسته‌ای که لازم داری رو بزن</p>
    </div>

    <?php if ($cats): ?>
      <div class="chiprow reveal">
        <?php foreach ($cats as $c): ?>
          <a class="chip" href="<?= url('shop.php?pet=' . urlencode($pet['slug']) . '&cat=' . urlencode($c['slug'])) ?>">
            <?= e($c['emoji']) ?> <?= e($c['title']) ?> <small><?= fa_num($c['n']) ?></small>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="prodgrid">
      <?php foreach ($prods as $p): ?>
        <div class="card reveal">
          <?php if ($p['badge']): ?>
            <span class="tag <?= $p['tone'] === 'blue' ? 'blue' : '' ?>"><?= e($p['badge']) ?></span>
          <?php endif; ?>
          <a href="<?= url('product.php?slug=' . urlencode($p['slug'])) ?>">
            <span class="ph <?= e($p['tone']) ?>">عکس محصول</span>
            <h3><?= e($p['title']) ?></h3>
          </a>
          <div class="row"><span class="price"><?= price_label($p['price']) ?></span></div>
          <a class="add" href="<?= url('product.php?slug=' . urlencode($p['slug'])) ?>">دیدن و خرید</a>
        </div>
      <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:26px" class="reveal">
      <a class="btn ghost" href="<?= url('shop.php?pet=' . urlencode($pet['slug'])) ?>">
        همه لوازم <?= e($pet['title']) ?></a>
    </div>
  </div>
</section>

<?php if ($posts): ?>
<section class="mag">
  <div class="wrap">
    <div class="sechead reveal"><h2 class="h2">راهنمای <?= e($pet['title']) ?></h2></div>
    <div class="maggrid">
      <?php foreach ($posts as $a): ?>
        <a class="post reveal" href="<?= url('article.php?slug=' . urlencode($a['slug'])) ?>">
          <div class="cover <?= e($a['tone']) ?>" aria-hidden="true"><?= $a['tone'] === 'blue' ? '🐾' : '🐰' ?></div>
          <div class="body">
            <div class="meta"><b><?= e($a['tag']) ?></b><span><?= fa_num($a['read_min']) ?> دقیقه</span></div>
            <h3><?= e($a['title']) ?></h3>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php endif; ?>
<?php require __DIR__ . '/inc/footer.php'; ?>
