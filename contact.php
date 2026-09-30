<?php
require_once __DIR__ . '/inc/functions.php';
$sent = false; $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $name = clean($_POST['name'], 60);
  $phone = valid_phone(isset($_POST['phone']) ? $_POST['phone'] : '');
  $pet = clean(isset($_POST['pet_type']) ? $_POST['pet_type'] : '', 40);
  $msg = clean(isset($_POST['message']) ? $_POST['message'] : '', 700);
  if ($name === '' || !$phone || $msg === '') {
    $err = 'اسم، شماره موبایل درست و متن پیام رو بنویس';
  } else {
    q('INSERT INTO leads (name,phone,pet_type,message,source,created_at) VALUES (?,?,?,?,?,?)',
      [$name, $phone, $pet, $msg, 'contact', date('Y-m-d H:i:s')]);
    $sent = true;
  }
}
$NAV = 'contact';
$pets = all('SELECT title FROM pets ORDER BY sort_order');
$PAGE_TITLE = 'تماس با ما — ' . $CFG['site_name'];
require __DIR__ . '/inc/header.php';
?>
<section class="pagehead">
  <div class="dust" aria-hidden="true"></div>
  <div class="wrap">
    <div class="crumb"><a href="<?= url('index.php') ?>">خانه</a> ← تماس</div>
    <h1 class="h2">تماس با ما</h1>
    <p class="lead">هر سؤالی داری بپرس، حتی اگه قرار نیست چیزی بخری.</p>
  </div>
</section>

<div class="wrap">
  <div class="cartlayout" style="padding-top:30px">
    <div class="summary" style="position:static">
      <h2 class="h3" style="margin-bottom:12px">پیام بذار</h2>
      <?php if ($sent): ?>
        <div class="note ok">پیامت رسید. تا یکی دو روز آینده جواب می‌دیم.</div>
        <a class="btn ghost" href="<?= url('index.php') ?>">برگرد به خانه</a>
      <?php else: ?>
        <?php if ($err): ?><div class="note err"><?= e($err) ?></div><?php endif; ?>
        <form method="post" data-once>
          <?= csrf_field() ?>
          <div class="two">
            <div class="field"><label for="n">اسمت</label>
              <input id="n" name="name" required maxlength="60"></div>
            <div class="field"><label for="p">شماره موبایل</label>
              <input id="p" name="phone" required inputmode="tel" maxlength="15"></div>
          </div>
          <div class="field"><label for="t">درباره کدوم حیوون</label>
            <select id="t" name="pet_type">
              <?php foreach ($pets as $p): ?><option value="<?= e($p['title']) ?>"><?= e($p['title']) ?></option><?php endforeach; ?>
              <option value="موضوع دیگه">موضوع دیگه</option>
            </select></div>
          <div class="field"><label for="m">پیامت</label>
            <textarea id="m" name="message" required maxlength="700" placeholder="راحت بنویس"></textarea></div>
          <button class="btn wide" type="submit">ارسال پیام</button>
        </form>
      <?php endif; ?>
    </div>

    <aside class="summary">
      <h3 class="h3" style="margin-bottom:12px">راه‌های دیگه</h3>
      <div class="trust">
        <div>📞 <span><b>تلفن</b><br><?= e(fa_num($CFG['phone'])) ?></span></div>
        <div>📱 <span><b>موبایل</b><br><?= e(fa_num($CFG['mobile'])) ?></span></div>
        <div>📍 <span><b>نشانی</b><br><?= e($CFG['address']) ?></span></div>
        <div>✉️ <span><b>ایمیل</b><br><?= e($CFG['email']) ?></span></div>
      </div>
      <a class="btn ghost wide" style="margin-top:14px" href="<?= e($CFG['instagram']) ?>"
         target="_blank" rel="noopener nofollow">اینستاگرام</a>
      <div class="note warn" style="margin-top:14px;font-size:12.5px">
        ساعت پاسخ‌گویی: شنبه تا پنجشنبه، ۱۰ تا ۱۹
      </div>
    </aside>
  </div>
</div>
<?php require __DIR__ . '/inc/footer.php'; ?>
