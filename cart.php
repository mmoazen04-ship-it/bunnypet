<?php
require_once __DIR__ . '/inc/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  if (isset($_POST['remove'])) {
    cart_set((int)$_POST['remove'], 0);
    flash('از سبد حذف شد', 'ok');
  } elseif (isset($_POST['qty']) && is_array($_POST['qty'])) {
    foreach ($_POST['qty'] as $pid => $v) { cart_set((int)$pid, (int)en_num($v)); }
    flash('سبد به‌روز شد', 'ok');
  } elseif (isset($_POST['clear'])) {
    $_SESSION['cart'] = [];
    flash('سبد خالی شد', 'ok');
  }
  redirect('cart.php');
}

$items = cart_items();
$total = cart_total();
$ship  = shipping_for($total);

$NAV = 'cart';
$PAGE_TITLE = 'سبد خرید — ' . $CFG['site_name'];
require __DIR__ . '/inc/header.php';
?>

<section class="pagehead" style="padding:26px 0 20px">
  <div class="dust" aria-hidden="true"></div>
  <div class="wrap">
    <div class="crumb"><a href="<?= url('index.php') ?>">خانه</a> ← سبد خرید</div>
    <h1 class="h2">سبد خرید</h1>
  </div>
</section>

<div class="wrap">
<?php if (!$items): ?>
  <div class="empty">
    <div class="big">🧺</div>
    <h2 class="h3">سبدت خالیه</h2>
    <p>یه سر به فروشگاه بزن، چیزهای خوبی هست.</p>
    <a class="btn" href="<?= url('shop.php') ?>">رفتن به فروشگاه</a>
  </div>
<?php else: ?>
  <form method="post" class="cartlayout">
    <?= csrf_field() ?>
    <div>
      <?php foreach ($items as $i): ?>
        <div class="citem">
          <span class="ph <?= e($i['tone']) ?>"></span>
          <div>
            <h3><a href="<?= url('product.php?slug=' . urlencode($i['slug'])) ?>"><?= e($i['title']) ?></a></h3>
            <div class="price"><?= price_label($i['price']) ?></div>
          </div>
          <div class="actions" style="display:flex;gap:10px;align-items:center">
            <label class="sr" for="q<?= (int)$i['id'] ?>">تعداد</label>
            <input id="q<?= (int)$i['id'] ?>" name="qty[<?= (int)$i['id'] ?>]" value="<?= (int)$i['qty'] ?>"
                   inputmode="numeric" maxlength="2"
                   style="width:58px;text-align:center;background:var(--cream);border:0;border-radius:12px;padding:9px">
            <button class="btn ghost mini" type="submit" name="remove" value="<?= (int)$i['id'] ?>">حذف</button>
          </div>
        </div>
      <?php endforeach; ?>
      <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:8px">
        <button class="btn ghost mini" type="submit">به‌روزرسانی سبد</button>
        <button class="btn ghost mini" type="submit" name="clear" value="1">خالی کردن سبد</button>
      </div>
    </div>

    <aside class="summary">
      <h3 class="h3" style="margin-bottom:12px">خلاصه سفارش</h3>
      <div class="line"><span>جمع کالاها</span><span><?= price_label($total) ?></span></div>
      <div class="line">
        <span>هزینه ارسال</span>
        <span><?= $ship === 0 ? 'رایگان' : price_label($ship) ?></span>
      </div>
      <?php if ($ship > 0): ?>
        <p class="lead" style="font-size:12.5px">
          تا <?= money($CFG['free_shipping_from'] - $total) ?> <?= e($CFG['currency']) ?> دیگه تا ارسال رایگان
        </p>
      <?php endif; ?>
      <div class="line total"><span>قابل پرداخت</span><span><?= price_label($total + $ship) ?></span></div>
      <a class="btn wide" style="margin-top:16px" href="<?= url('checkout.php') ?>">ادامه و پرداخت</a>
      <a class="btn ghost wide" style="margin-top:9px" href="<?= url('shop.php') ?>">ادامه خرید</a>
    </aside>
  </form>
<?php endif; ?>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>
