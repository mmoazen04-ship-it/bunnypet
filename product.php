<?php
require_once __DIR__ . '/inc/functions.php';

$slug = isset($_GET['slug']) ? clean($_GET['slug'], 80) : '';
$p = one('SELECT p.*, c.title ctitle, c.slug cslug, pt.title ptitle, pt.slug pslug
          FROM products p JOIN categories c ON c.id = p.cat_id
          LEFT JOIN pets pt ON pt.id = p.pet_id
          WHERE p.slug = ? AND p.active = 1', [$slug]);
if (!$p) { http_response_code(404); }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $p) {
  csrf_check();
  $qty = isset($_POST['qty']) ? (int)$_POST['qty'] : 1;
  if (cart_add($p['id'], $qty)) { flash('به سبد اضافه شد', 'ok'); redirect('cart.php'); }
  flash('این محصول فعلاً در دسترس نیست', 'err');
}

$NAV = 'shop';
$PETNAV = $p ? $p['pslug'] : '';
$PAGE_TITLE = ($p ? $p['title'] : 'پیدا نشد') . ' — ' . $CFG['site_name'];
$PAGE_DESC  = $p ? $p['short'] : '';
if ($p) {
  $CANON = url('product.php?slug=' . $p['slug']);
  $JSONLD = [
    '@context' => 'https://schema.org', '@type' => 'Product',
    'name' => $p['title'], 'description' => $p['short'],
    'category' => $p['ctitle'],
    'offers' => [
      '@type' => 'Offer', 'price' => (int)$p['price'], 'priceCurrency' => 'IRT',
      'availability' => ((int)$p['stock'] > 0
        ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock'),
      'url' => $CANON,
    ],
  ];
}
require __DIR__ . '/inc/header.php';
?>

<?php if (!$p): ?>
  <div class="empty"><div class="big">🔍</div>
    <h1 class="h2">این صفحه پیدا نشد</h1>
    <a class="btn" href="<?= url('shop.php') ?>">فروشگاه</a></div>
<?php else: ?>

<section class="pagehead" style="padding:22px 0 18px">
  <div class="dust" aria-hidden="true"></div>
  <div class="wrap">
    <div class="crumb">
      <a href="<?= url('index.php') ?>">خانه</a> ←
      <?php if ($p['pslug']): ?>
        <a href="<?= url('pet.php?slug=' . urlencode($p['pslug'])) ?>"><?= e($p['ptitle']) ?></a> ←
      <?php endif; ?>
      <a href="<?= url('shop.php?cat=' . urlencode($p['cslug'])) ?>"><?= e($p['ctitle']) ?></a> ←
      <?= e($p['title']) ?>
    </div>
  </div>
</section>

<div class="wrap">
  <div class="pdp">
    <div class="gal ph <?= e($p['tone']) ?>" style="position:relative">
      <span>عکس محصول</span>
      <?php if ($p['badge']): ?><span class="tag"><?= e($p['badge']) ?></span><?php endif; ?>
    </div>

    <div>
      <h1><?= e($p['title']) ?></h1>
      <p class="desc"><?= e($p['short']) ?></p>

      <div class="specs">
        <div><small>دسته</small><b><?= e($p['ctitle']) ?></b></div>
        <?php if ($p['ptitle']): ?><div><small>مناسب</small><b><?= e($p['ptitle']) ?></b></div><?php endif; ?>
        <div><small>موجودی</small><b><?= (int)$p['stock'] > 0 ? 'موجود' : 'ناموجود' ?></b></div>
      </div>

      <div class="pprice"><?= price_label($p['price']) ?></div>
      <?php if ((int)$p['old_price'] > 0): ?>
        <p class="lead" style="margin-top:-10px">قیمت قبلی:
          <span style="text-decoration:line-through"><?= money($p['old_price']) ?></span></p>
      <?php endif; ?>

      <form method="post" data-once style="margin-top:18px">
        <?= csrf_field() ?>
        <input type="hidden" name="qty" value="1">
        <div class="qty">
          <button type="button" data-step="down" aria-label="کمتر">−</button>
          <span>۱</span>
          <button type="button" data-step="up" aria-label="بیشتر">+</button>
        </div>
        <div class="btns">
          <button class="btn" type="submit">افزودن به سبد</button>
          <a class="btn ghost" href="<?= url('index.php#join') ?>">اول مشورت می‌خوام</a>
        </div>
      </form>

      <div class="trust">
        <div>🚚 <span><b>ارسال به سراسر کشور</b> — بالای <?= money($CFG['free_shipping_from']) ?> <?= e($CFG['currency']) ?> رایگان</span></div>
        <div>🩺 <span><b>مشاوره قبل و بعد از خرید</b> — بپرس تا راهنماییت کنیم</span></div>
        <div>🔒 <span><b>پرداخت امن</b> — درگاه بانکی و نماد اعتماد الکترونیکی</span></div>
      </div>
    </div>
  </div>

  <?php if (trim((string)$p['body']) !== ''): ?>
    <div class="article" style="padding-top:0">
      <div class="content"><h2>درباره این محصول</h2><p><?= nl2br(e($p['body'])) ?></p></div>
    </div>
  <?php endif; ?>

  <?php
  $rel = all('SELECT * FROM products WHERE cat_id = ? AND id <> ? AND active = 1 LIMIT 4',
             [$p['cat_id'], $p['id']]);
  if ($rel): ?>
    <section style="padding-bottom:50px">
      <div class="sechead"><h2 class="h3">اینا هم به دردت می‌خوره</h2></div>
      <div class="prodgrid">
        <?php foreach ($rel as $r): ?>
          <div class="card reveal">
            <a href="<?= url('product.php?slug=' . urlencode($r['slug'])) ?>">
              <span class="ph <?= e($r['tone']) ?>">عکس محصول</span>
              <h3><?= e($r['title']) ?></h3>
            </a>
            <div class="row"><span class="price"><?= price_label($r['price']) ?></span></div>
            <a class="add" href="<?= url('product.php?slug=' . urlencode($r['slug'])) ?>">دیدن</a>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</div>

<?php endif; ?>
<?php require __DIR__ . '/inc/footer.php'; ?>
