<?php
require_once __DIR__ . '/inc/functions.php';
require_once __DIR__ . '/inc/gateways.php';

$code  = isset($_GET['code']) ? clean($_GET['code'], 30) : '';
$order = one('SELECT * FROM orders WHERE code = ?', [$code]);
if (!$order) { flash('سفارش پیدا نشد', 'err'); redirect('index.php'); }

/* اگه قبلاً تأیید شده، دوباره تأیید نمی‌کنیم */
if ($order['status'] !== 'paid') {
  $ok = gw_verify($order);
  q('UPDATE orders SET status = ? WHERE id = ?', [$ok ? 'paid' : 'failed', $order['id']]);
  $order['status'] = $ok ? 'paid' : 'failed';
}
$paid = ($order['status'] === 'paid');

$NAV = '';
$PAGE_TITLE = ($paid ? 'پرداخت موفق' : 'پرداخت ناموفق') . ' — ' . $CFG['site_name'];
require __DIR__ . '/inc/header.php';
?>

<div class="authwrap" style="max-width:580px">
  <div class="authcard" style="text-align:center">
    <div style="font-size:58px;line-height:1"><?= $paid ? '🎉' : '😕' ?></div>
    <h1 class="h2" style="margin-top:10px">
      <?= $paid ? 'پرداخت انجام شد' : 'پرداخت انجام نشد' ?>
    </h1>
    <p class="lead">
      <?= $paid
        ? 'مرسی که به ما اعتماد کردی. به‌زودی برای هماهنگی ارسال باهات تماس می‌گیریم.'
        : 'پولی از حسابت کم نشده. اگه کم شده بود، تا ۷۲ ساعت برمی‌گرده.' ?>
    </p>

    <div class="note <?= $paid ? 'ok' : 'err' ?>" style="text-align:right;margin-top:18px">
      شماره سفارش: <b><?= e(fa_num($order['code'])) ?></b><br>
      مبلغ: <?= price_label($order['total']) ?><br>
      <?php if ($paid && $order['ref_id']): ?>کد پیگیری: <?= e(fa_num($order['ref_id'])) ?><?php endif; ?>
    </div>

    <div class="btns" style="justify-content:center">
      <?php if ($paid): ?>
        <a class="btn" href="<?= url('account.php') ?>">سفارش‌های من</a>
        <a class="btn ghost" href="<?= url('shop.php') ?>">ادامه خرید</a>
      <?php else: ?>
        <a class="btn" href="<?= url('checkout.php') ?>">تلاش دوباره</a>
        <a class="btn ghost" href="<?= url('index.php') ?>">خانه</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>
