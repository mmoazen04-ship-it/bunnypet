<?php
require_once __DIR__ . '/inc/functions.php';

$slug = isset($_GET['slug']) ? clean($_GET['slug'], 80) : '';
$a = one('SELECT a.*, pt.title ptitle, pt.slug pslug FROM animals a
          JOIN pets pt ON pt.id = a.pet_id WHERE a.slug = ? AND a.active = 1', [$slug]);
if (!$a) { http_response_code(404); }

$sent = false;
$err  = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $a) {
  csrf_check();
  $U     = current_user();
  $name  = clean($_POST['name'], 60);
  $phone = valid_phone(isset($_POST['phone']) ? $_POST['phone'] : '');
  $city  = clean(isset($_POST['city']) ? $_POST['city'] : '', 60);
  $home  = clean(isset($_POST['home']) ? $_POST['home'] : '', 100);
  $exp   = clean(isset($_POST['experience']) ? $_POST['experience'] : '', 500);
  if ($name === '' || !$phone) {
    $err = 'اسم و شماره موبایل درست رو بنویس';
  } else {
    q('INSERT INTO adoptions (code,animal_id,user_id,name,phone,city,home,experience,status,created_at)
       VALUES (?,?,?,?,?,?,?,?,?,?)',
      ['AD-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5)),
       $a['id'], $U ? $U['id'] : null, $name, $phone, $city, $home, $exp, 'new',
       date('Y-m-d H:i:s')]);
    $sent = true;
  }
}

$NAV = 'adopt';
$PETNAV = $a ? $a['pslug'] : '';
$PAGE_TITLE = ($a ? $a['name'] . ' — سرپرستی' : 'پیدا نشد') . ' — ' . $CFG['site_name'];
if ($a) {
  $PAGE_DESC = mb_substr($a['story'], 0, 150, 'UTF-8');
  $CANON = url('animal.php?slug=' . $a['slug']);
}
require __DIR__ . '/inc/header.php';
?>

<?php if (!$a): ?>
  <div class="empty"><div class="big">🐾</div>
    <h1 class="h2">این پرونده پیدا نشد</h1>
    <a class="btn" href="<?= url('adopt.php') ?>">حیوون‌های آماده سرپرستی</a></div>
<?php else: ?>

<section class="pagehead" style="padding:22px 0 18px">
  <div class="dust" aria-hidden="true"></div>
  <div class="wrap">
    <div class="crumb">
      <a href="<?= url('index.php') ?>">خانه</a> ←
      <a href="<?= url('adopt.php') ?>">سرپرستی</a> ←
      <a href="<?= url('pet.php?slug=' . urlencode($a['pslug'])) ?>"><?= e($a['ptitle']) ?></a> ←
      <?= e($a['name']) ?>
    </div>
  </div>
</section>

<div class="wrap">
  <div class="pdp">
    <div class="gal ph <?= e($a['tone']) ?>" style="position:relative">
      <span><?= e($a['name']) ?></span>
      <span class="tag <?= $a['status'] === 'ready' ? '' : 'blue' ?>"><?= e(status_label($a['status'])) ?></span>
    </div>

    <div>
      <h1><?= e($a['name']) ?></h1>
      <p class="desc"><?= e($a['story']) ?></p>

      <div class="specs">
        <div><small>نژاد</small><b><?= e($a['breed']) ?></b></div>
        <div><small>سن</small><b><?= e($a['age']) ?></b></div>
        <div><small>جنسیت</small><b><?= e($a['sex']) ?></b></div>
        <div><small>خانواده</small><b><?= e($a['ptitle']) ?></b></div>
      </div>

      <div class="worthbox">
        <span>ارزش سرپرستی</span>
        <b><?= price_label($a['worth']) ?></b>
        <small>شامل معاینه و واکسن انجام‌شده تا امروز</small>
      </div>

      <div class="trust">
        <div>🩺 <span><b>سلامت بررسی شده</b> — <?= e($a['care']) ?></span></div>
        <div>🤝 <span><b>تحویل حضوری</b> — هیچ حیوونی با پست جابه‌جا نمی‌شه</span></div>
        <div>📞 <span><b>پیگیری بعد از تحویل</b> — روز دوم و روز هفتم</span></div>
      </div>

      <?php if ($a['status'] === 'ready'): ?>
        <a class="btn" style="margin-top:20px" href="#form">درخواست سرپرستی</a>
      <?php else: ?>
        <div class="note warn" style="margin-top:20px">
          <?= e($a['name']) ?> الان <?= e(status_label($a['status'])) ?>.
          می‌تونی بقیه حیوون‌ها رو ببینی یا شماره‌ت رو بذاری تا بعدی رو بهت خبر بدیم.
        </div>
        <a class="btn ghost" href="<?= url('adopt.php') ?>">بقیه حیوون‌ها</a>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($a['status'] === 'ready'): ?>
  <section id="form" style="padding-bottom:50px">
    <div class="summary" style="position:static;max-width:640px;margin:0 auto">
      <h2 class="h3" style="margin-bottom:6px">درخواست سرپرستی <?= e($a['name']) ?></h2>
      <p class="lead" style="margin-bottom:14px">
        این فرم یعنی «بیا حرف بزنیم»، نه یه سفارش قطعی. بعدش باهات تماس می‌گیریم.
      </p>

      <?php if ($sent): ?>
        <div class="note ok">
          درخواستت ثبت شد. تا یکی دو روز آینده باهات تماس می‌گیریم و درباره‌ش حرف می‌زنیم.
        </div>
        <a class="btn ghost" href="<?= url('adopt.php') ?>">بقیه حیوون‌ها</a>
      <?php else: ?>
        <?php if ($err): ?><div class="note err"><?= e($err) ?></div><?php endif; ?>
        <form method="post" data-once>
          <?= csrf_field() ?>
          <div class="two">
            <div class="field"><label for="n">اسم و فامیل</label>
              <input id="n" name="name" required maxlength="60"></div>
            <div class="field"><label for="p">شماره موبایل</label>
              <input id="p" name="phone" required inputmode="tel" maxlength="15"></div>
          </div>
          <div class="two">
            <div class="field"><label for="c">شهر</label>
              <input id="c" name="city" maxlength="60" placeholder="تهران"></div>
            <div class="field"><label for="h">خونه‌ت چه شکلیه</label>
              <select id="h" name="home">
                <option>آپارتمان</option><option>خونه حیاط‌دار</option>
                <option>خوابگاه یا خونه مشترک</option>
              </select></div>
          </div>
          <div class="field"><label for="x">قبلاً حیوون نگه داشتی؟ چی؟</label>
            <textarea id="x" name="experience" maxlength="500"
              placeholder="راحت بنویس؛ جواب درست و غلط نداره"></textarea></div>
          <button class="btn wide" type="submit">ثبت درخواست سرپرستی</button>
        </form>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php
  $rel = all("SELECT * FROM animals WHERE pet_id = ? AND id <> ? AND active = 1 AND status = 'ready' LIMIT 4",
             [$a['pet_id'], $a['id']]);
  if ($rel): ?>
    <section style="padding-bottom:50px">
      <div class="sechead"><h2 class="h3">اینا هم منتظرن</h2></div>
      <div class="prodgrid">
        <?php foreach ($rel as $r): ?>
          <div class="card reveal">
            <a href="<?= url('animal.php?slug=' . urlencode($r['slug'])) ?>">
              <span class="ph <?= e($r['tone']) ?>"><?= e($r['name']) ?></span>
              <h3><?= e($r['name']) ?> — <?= e($r['breed']) ?></h3>
            </a>
            <div class="row">
              <span class="worthlabel">ارزش سرپرستی</span>
              <span class="price"><?= price_label($r['worth']) ?></span>
            </div>
            <a class="add" href="<?= url('animal.php?slug=' . urlencode($r['slug'])) ?>">آشنا شو</a>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</div>

<?php endif; ?>
<?php require __DIR__ . '/inc/footer.php'; ?>
