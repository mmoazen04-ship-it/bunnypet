<?php
require_once __DIR__ . '/inc/functions.php';
require_once __DIR__ . '/inc/gateways.php';

$items = cart_items();
if (!$items) { flash('سبدت خالیه', 'warn'); redirect('shop.php'); }

$total = cart_total();
$ship  = shipping_for($total);
$pay   = $total + $ship;
$gws   = gw_list();
$U     = current_user();
$err   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $name    = clean($_POST['name'], 60);
  $phone   = valid_phone(isset($_POST['phone']) ? $_POST['phone'] : '');
  $address = clean(isset($_POST['address']) ? $_POST['address'] : '', 400);
  $note    = clean(isset($_POST['note']) ? $_POST['note'] : '', 400);
  $gw      = isset($_POST['gateway']) ? clean($_POST['gateway'], 20) : '';

  if ($name === '' || !$phone || mb_strlen($address, 'UTF-8') < 10) {
    $err = 'اسم، شماره موبایل درست و نشانی کامل رو بنویس';
  } elseif (!isset($gws[$gw])) {
    $err = 'روش پرداخت رو انتخاب کن';
  } else {
    $code = order_code();
    q('INSERT INTO orders (code,user_id,name,phone,address,note,items_total,shipping,total,gateway,status,created_at)
       VALUES (?,?,?,?,?,?,?,?,?,?,?,?)',
      [$code, $U ? $U['id'] : null, $name, $phone, $address, $note,
       $total, $ship, $pay, $gw, 'pending', date('Y-m-d H:i:s')]);
    $oid = last_id();
    foreach ($items as $i) {
      q('INSERT INTO order_items (order_id, product_id, title, price, qty) VALUES (?,?,?,?,?)',
        [$oid, $i['id'], $i['title'], $i['price'], $i['qty']]);
    }
    $_SESSION['cart'] = [];
    redirect('pay.php?code=' . urlencode($code));
  }
}

$NAV = 'cart';
$PAGE_TITLE = 'تکمیل خرید — ' . $CFG['site_name'];
require __DIR__ . '/inc/header.php';
?>

<section class="pagehead" style="padding:26px 0 20px">
  <div class="dust" aria-hidden="true"></div>
  <div class="wrap">
    <div class="crumb"><a href="<?= url('cart.php') ?>">سبد خرید</a> ← تکمیل خرید</div>
    <h1 class="h2">تکمیل خرید</h1>
  </div>
</section>

<div class="wrap">
  <form method="post" class="cartlayout" data-once>
    <?= csrf_field() ?>
    <div>
      <?php if ($err): ?><div class="note err"><?= e($err) ?></div><?php endif; ?>

      <div class="summary" style="position:static;margin-bottom:18px">
        <h3 class="h3" style="margin-bottom:14px">اطلاعات گیرنده</h3>
        <div class="two">
          <div class="field">
            <label for="n">اسم و فامیل</label>
            <input id="n" name="name" required maxlength="60"
                   value="<?= e(isset($_POST['name']) ? $_POST['name'] : ($U ? $U['name'] : '')) ?>">
          </div>
          <div class="field">
            <label for="p">شماره موبایل</label>
            <input id="p" name="phone" required inputmode="tel" maxlength="15"
                   value="<?= e(isset($_POST['phone']) ? $_POST['phone'] : ($U ? $U['phone'] : '')) ?>">
          </div>
        </div>
        <div class="field">
          <label for="a">نشانی کامل</label>
          <textarea id="a" name="address" required maxlength="400"
            placeholder="شهر، خیابان، پلاک، واحد و کد پستی"><?= e(isset($_POST['address']) ? $_POST['address'] : '') ?></textarea>
        </div>
        <div class="field">
          <label for="ds">توضیح برای ما (اختیاری)</label>
          <textarea id="ds" name="note" maxlength="400"
            placeholder="مثلاً ساعت تحویل یا سؤالی که داری"><?= e(isset($_POST['note']) ? $_POST['note'] : '') ?></textarea>
        </div>
      </div>

      <div class="summary" style="position:static">
        <h3 class="h3" style="margin-bottom:6px">روش پرداخت</h3>
        <p class="lead" style="margin-bottom:12px">هر کدوم راحت‌تری</p>
        <div class="paylist">
          <?php $first = true; foreach ($gws as $k => $g): ?>
            <label class="pay<?= $first ? ' on' : '' ?>">
              <input type="radio" name="gateway" value="<?= e($k) ?>" <?= $first ? 'checked' : '' ?> required>
              <span class="mark <?= e($g['logo']) ?>">
                <?= e($k === 'zarinpal' ? 'زرین' : ($k === 'card' ? 'کارت' : ($k === 'cod' ? 'درمحل' : $k))) ?>
              </span>
              <span class="t">
                <b><?= e($g['title']) ?></b>
                <small><?= e($g['note']) ?></small>
              </span>
            </label>
          <?php $first = false; endforeach; ?>
        </div>
        <?php if (!empty($CFG['sandbox'])): ?>
          <div class="note warn">
            سایت توی حالت آزمایشیه؛ پول واقعی جابه‌جا نمی‌شه. برای فعال کردن پرداخت واقعی،
            مرچنت‌کد رو توی <code>inc/config.php</code> بذار و <code>sandbox</code> رو false کن.
          </div>
        <?php endif; ?>
      </div>
    </div>

    <aside class="summary">
      <h3 class="h3" style="margin-bottom:12px">سفارش تو</h3>
      <?php foreach ($items as $i): ?>
        <div class="line">
          <span><?= e(excerpt($i['title'], 26)) ?> × <?= fa_num($i['qty']) ?></span>
          <span><?= money($i['sum']) ?></span>
        </div>
      <?php endforeach; ?>
      <div class="line"><span>ارسال</span><span><?= $ship === 0 ? 'رایگان' : money($ship) ?></span></div>
      <div class="line total"><span>قابل پرداخت</span><span><?= price_label($pay) ?></span></div>
      <button class="btn wide" style="margin-top:16px" type="submit">ثبت سفارش و پرداخت</button>
      <p class="lead" style="font-size:12px;margin-top:10px">
        با ثبت سفارش، شماره‌ت برای پیگیری و مشاوره ذخیره می‌شه.
      </p>
    </aside>
  </form>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>
