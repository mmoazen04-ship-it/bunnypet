<?php
require_once __DIR__ . '/inc/functions.php';
$NAV = 'about';
$PAGE_TITLE = 'درباره بانی‌پت — ' . $CFG['site_name'];
$PAGE_DESC = 'ما کی هستیم و چطور کار می‌کنیم.';
$pets = all('SELECT * FROM pets ORDER BY sort_order');
require __DIR__ . '/inc/header.php';
?>
<section class="pagehead">
  <div class="dust" aria-hidden="true"></div>
  <span class="blob f1" style="width:150px;height:150px;top:-40px;right:9%;background:#fff;opacity:.5"></span>
  <div class="wrap">
    <div class="crumb"><a href="<?= url('index.php') ?>">خانه</a> ← درباره ما</div>
    <h1 class="h2">ما کی هستیم</h1>
    <p class="lead" style="max-width:620px">یه پت‌شاپ که با خرگوش شروع شد و حالا کنار همه حیوون‌های خونگیه.</p>
  </div>
</section>

<div class="article">
  <div class="content">
    <p>بانی‌پت با نگهداری و واگذاری خرگوش مینی‌لوپ شروع شد. توی این مسیر یه چیز رو مدام دیدیم: آدم‌ها حیوون رو دوست دارن، ولی کسی بهشون نگفته بود نگهداریش واقعاً چی لازم داره. خیلی‌ها بعد از چند ماه کم میاوردن، نه چون بی‌مسئولیت بودن، بلکه چون از اول اطلاعات درست نگرفته بودن.</p>
    <p>برای همین بانی‌پت شد یه پت‌شاپ کامل: غذا، بهداشت، لانه، حمل و همه لوازم روزمره همه حیوون‌های خونگی؛ و در کنارش سرپرستی حیوون، با راهنمایی قبل و بعدش.</p>

    <h2>چطور کار می‌کنیم</h2>
    <ul>
      <li>حیوون زنده هیچ‌وقت با پست جابه‌جا نمی‌شه. تحویل فقط حضوریه.</li>
      <li>قبل از سرپرستی حرف می‌زنیم. اگه شرایطت جور نباشه، صادقانه می‌گیم صبر کن.</li>
      <li>روز دوم و روز هفتم بعد از تحویل پیگیری می‌کنیم.</li>
      <li>چیزی رو که حیوونت لازم نداره بهت پیشنهاد نمی‌دیم.</li>
    </ul>

    <h2>چه حیوون‌هایی</h2>
    <p>شش خانواده: <?php $t=[]; foreach($pets as $p){$t[]=$p['title'];} echo e(implode('، ', $t)); ?>. برای بعضی‌ها هم لوازم داریم هم سرپرستی، برای بعضی‌ها فقط لوازم.</p>

    <h2>چیزی که قول نمی‌دیم</h2>
    <p>قول نمی‌دیم ارزون‌ترین جای بازار باشیم. چیزی که قول می‌دیم اینه که وقتی سؤالی داشتی، جواب واقعی بگیری؛ حتی اگه اون جواب به ضرر فروش ما باشه.</p>
  </div>

  <div class="summary" style="position:static;margin-top:30px;text-align:center">
    <h3 class="h3">سؤالی داری؟</h3>
    <p class="lead">یه تماس کوتاه بگیر یا شماره‌ت رو بذار تا ما تماس بگیریم.</p>
    <div class="btns" style="justify-content:center">
      <a class="btn" href="<?= url('contact.php') ?>">تماس با ما</a>
      <a class="btn ghost" href="<?= url('faq.php') ?>">پرسش‌های پرتکرار</a>
    </div>
  </div>
</div>
<?php require __DIR__ . '/inc/footer.php'; ?>
