<?php
require_once __DIR__ . '/inc/functions.php';

$catSlug = isset($_GET['cat']) ? clean($_GET['cat'], 60) : '';
$petSlug = isset($_GET['pet']) ? clean($_GET['pet'], 60) : '';
$term    = isset($_GET['q'])   ? clean($_GET['q'], 60)   : '';

$cat = $catSlug !== '' ? one('SELECT * FROM categories WHERE slug = ?', [$catSlug]) : null;
$pet = $petSlug !== '' ? one('SELECT * FROM pets WHERE slug = ?', [$petSlug]) : null;

$sql = 'SELECT p.* FROM products p WHERE p.active = 1';
$par = [];
if ($cat) { $sql .= ' AND p.cat_id = ?'; $par[] = $cat['id']; }
if ($pet) { $sql .= ' AND p.pet_id = ?'; $par[] = $pet['id']; }
if ($term !== '') {
  $sql .= ' AND (p.title LIKE ? OR p.short LIKE ?)';
  $par[] = '%' . $term . '%'; $par[] = '%' . $term . '%';
}
$sql .= ' ORDER BY p.id';
$items = all($sql, $par);

$cats = all('SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.cat_id = c.id AND p.active = 1) n
             FROM categories c ORDER BY c.sort_order');
$pets = all('SELECT p.*, (SELECT COUNT(*) FROM products x WHERE x.pet_id = p.id AND x.active = 1) n
             FROM pets p ORDER BY p.sort_order');

function shop_url($cat, $pet) {
  $qs = [];
  if ($cat) $qs['cat'] = $cat;
  if ($pet) $qs['pet'] = $pet;
  return url('shop.php' . ($qs ? '?' . http_build_query($qs) : ''));
}

$NAV = 'shop';
$PETNAV = $petSlug;
$title = $cat ? $cat['title'] : 'همه محصول‌ها';
if ($pet) { $title .= ' — ' . $pet['title']; }
$PAGE_TITLE = $title . ' — ' . $CFG['site_name'];
require __DIR__ . '/inc/header.php';
?>

<section class="pagehead">
  <div class="dust" aria-hidden="true"></div>
  <span class="blob f1" style="width:150px;height:150px;top:-40px;left:8%;background:#fff;opacity:.5"></span>
  <div class="wrap">
    <div class="crumb">
      <a href="<?= url('index.php') ?>">خانه</a> ←
      <a href="<?= url('shop.php') ?>">فروشگاه</a>
      <?= $cat ? ' ← ' . e($cat['title']) : '' ?>
      <?= $pet ? ' ← ' . e($pet['title']) : '' ?>
    </div>
    <h1 class="h2"><?= e($title) ?></h1>
    <p class="lead">
      <?php if ($term !== ''): ?>
        نتیجه جستجو برای «<?= e($term) ?>» — <?= fa_num(count($items)) ?> مورد
      <?php elseif ($cat): ?><?= e($cat['subtitle']) ?>
      <?php else: ?>ده دسته لوازم، برای شش خانواده حیوون<?php endif; ?>
    </p>
  </div>
</section>

<div class="wrap">
  <!-- فیلتر لایه ۱: خانواده -->
  <div class="chiprow" style="padding-top:22px">
    <a class="chip<?= $pet ? '' : ' on' ?>" href="<?= shop_url($catSlug, '') ?>">همه حیوون‌ها</a>
    <?php foreach ($pets as $p): if (!$p['n']) continue; ?>
      <a class="chip<?= ($pet && $pet['id'] == $p['id']) ? ' on' : '' ?>"
         href="<?= shop_url($catSlug, $p['slug']) ?>">
        <?= e($p['emoji']) ?> <?= e($p['title']) ?> <small><?= fa_num($p['n']) ?></small></a>
    <?php endforeach; ?>
  </div>

  <div class="layout">
    <!-- فیلتر لایه ۲: دسته لوازم -->
    <aside class="side">
      <h3>دسته‌های لوازم</h3>
      <ul>
        <li><a href="<?= shop_url('', $petSlug) ?>"<?= active_if(!$cat) ?>>همه دسته‌ها</a></li>
        <?php foreach ($cats as $c): ?>
          <li><a href="<?= shop_url($c['slug'], $petSlug) ?>"<?= active_if($cat && $cat['id'] == $c['id']) ?>>
            <span><?= e($c['emoji']) ?> <?= e($c['title']) ?></span><small><?= fa_num($c['n']) ?></small></a></li>
        <?php endforeach; ?>
      </ul>
      <?php if ($pet && $pet['adopt']): ?>
        <a class="btn ghost wide mini" style="margin-top:14px"
           href="<?= url('adopt.php?pet=' . urlencode($pet['slug'])) ?>">
          سرپرستی <?= e($pet['title']) ?></a>
      <?php endif; ?>
    </aside>

    <div>
      <?php if (!$items): ?>
        <div class="empty"><div class="big">🐾</div>
          <p>با این فیلتر چیزی پیدا نشد. یه دسته دیگه رو امتحان کن.</p>
          <a class="btn" href="<?= url('shop.php') ?>">همه محصول‌ها</a></div>
      <?php else: ?>
        <div class="prodgrid three">
          <?php foreach ($items as $p): ?>
            <div class="card reveal">
              <?php if ($p['badge']): ?>
                <span class="tag <?= $p['tone'] === 'blue' ? 'blue' : '' ?>"><?= e($p['badge']) ?></span>
              <?php endif; ?>
              <a href="<?= url('product.php?slug=' . urlencode($p['slug'])) ?>">
                <span class="ph <?= e($p['tone']) ?>">عکس محصول</span>
                <h3><?= e($p['title']) ?></h3>
              </a>
              <div class="row">
                <span class="price"><?= price_label($p['price']) ?></span>
                <?php if ((int)$p['old_price'] > 0): ?>
                  <span class="old"><?= money($p['old_price']) ?></span>
                <?php endif; ?>
              </div>
              <a class="add" href="<?= url('product.php?slug=' . urlencode($p['slug'])) ?>">دیدن و خرید</a>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>
