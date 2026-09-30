<?php
require_once __DIR__ . '/inc/functions.php';
require_once __DIR__ . '/inc/gateways.php';

$code  = isset($_GET['code']) ? clean($_GET['code'], 30) : '';
$order = one('SELECT * FROM orders WHERE code = ?', [$code]);
if (!$order) { flash('سفارش پیدا نشد', 'err'); redirect('index.php'); }

$gws = gw_list();
$g   = isset($gws[$order['gateway']]) ? $gws[$order['gateway']] : null;
$res = ($order['status'] === 'pending') ? gw_start($order) : ['done' => true];

if (isset($res['go'])) { header('Location: ' . $res['go']); exit; }

$NAV = 'cart';
$PAGE_TITLE = 'پرداخت سفارش — ' . $CFG['site_name'];
require __DIR__ . '/inc/header.php';
?>

<div class="authwrap" style="max-width:600px">
  <div class="authcard">
    <span class="kicker">سفارش <?= e(fa_num($order['code'])) ?></span>
    <h1 class="h2">
      <?php if (isset($res['err'])): ?>پرداخت انجام نشد
      <?php elseif ($order['gateway'] === 'cod'): ?>سفارشت ثبت شد
      <?php elseif ($order['gateway'] === 'card'): ?>کارت به کارت
      <?php else: ?>یه قدم تا پرداخت<?php endif; ?>
    </h1>

    <div class="line" style="display:flex;justify-content:space-between;padding:10px 0;font-weight:700">
      <span>مبلغ قابل پرداخت</span><span style="color:var(--pink)"><?= price_label($order['total']) ?></span>
    </div>

    <?php if (isset($res['err'])): ?>
      <div class="note err"><?= e($res['err']) ?></div>
      <a class="btn" href="<?= url('checkout.php') ?>">امتحان دوباره</a>

    <?php elseif ($order['gateway'] === 'card'): ?>
      <div class="note ok">
        مبلغ بالا رو به این کارت واریز کن و بعدش رسیدش رو برامون بفرست:<br><br>
        <b style="font-size:18px;letter-spacing:2px"><?= e($g['card_no']) ?></b><br>
        به نام <?= e($g['card_name']) ?>
      </div>
      <p class="lead">بعد از تأیید واریز، سفارش برات ارسال می‌شه. شماره سفارشت رو یادداشت کن.</p>
      <a class="btn" href="<?= url('account.php') ?>">سفارش‌های من</a>

    <?php elseif ($order['gateway'] === 'cod'): ?>
      <div class="note ok">
        سفارشت ثبت شد. موقع تحویل، مبلغ رو نقدی یا کارتخوان پرداخت می‌کنی.
      </div>
      <p class="lead">برای هماهنگی زمان تحویل باهات تماس می‌گیریم.</p>
      <a class="btn" href="<?= url('index.php') ?>">برگشت به خانه</a>

    <?php elseif (isset($res['sandbox'])): ?>
      <div class="note warn">
        <b>حالت آزمایشی</b> — این صفحه جای درگاه بانکه. با دکمه‌های زیر می‌تونی هر دو حالت
        موفق و ناموفق رو ببینی. بعد از گرفتن مرچنت‌کد، این صفحه خودکار حذف می‌شه.
      </div>
      <div class="btns">
        <a class="btn" href="<?= url('callback.php?code=' . urlencode($order['code']) . '&ok=1') ?>">
          شبیه‌سازی پرداخت موفق
        </a>
        <a class="btn ghost" href="<?= url('callback.php?code=' . urlencode($order['code']) . '&ok=0') ?>">
          پرداخت ناموفق
        </a>
      </div>

    <?php else: ?>
      <p class="lead">داری به درگاه بانکی منتقل می‌شی…</p>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>
