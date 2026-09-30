<?php
require_once __DIR__ . '/inc/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join'])) {
  csrf_check();
  $name  = clean($_POST['name'], 60);
  $phone = valid_phone(isset($_POST['phone']) ? $_POST['phone'] : '');
  $pet   = clean(isset($_POST['pet_type']) ? $_POST['pet_type'] : '', 40);
  if ($name === '' || !$phone) {
    flash('اسم و شماره موبایل درست رو بنویس', 'err');
  } else {
    q('INSERT INTO leads (name, phone, pet_type, message, source, created_at) VALUES (?,?,?,?,?,?)',
      [$name, $phone, $pet, '', 'home', date('Y-m-d H:i:s')]);
    flash('ثبت شد. تا یکی دو روز آینده باهات تماس می‌گیریم', 'ok');
  }
  redirect('index.php#join');
}

$NAV   = 'home';
$pets  = all('SELECT * FROM pets ORDER BY sort_order');
$cats  = all('SELECT * FROM categories ORDER BY sort_order');
$best  = all("SELECT p.* FROM products p WHERE p.active = 1
              ORDER BY CASE WHEN p.badge IS NULL OR p.badge = '' THEN 1 ELSE 0 END, p.id LIMIT 8");
$anims = all("SELECT a.*, pt.title ptitle, pt.slug pslug FROM animals a
              JOIN pets pt ON pt.id = a.pet_id
              WHERE a.active = 1 AND a.status = 'ready' ORDER BY a.id LIMIT 4");
$posts = all('SELECT * FROM articles WHERE active = 1 ORDER BY created_at DESC LIMIT 3');
$JSONLD = [
  '@context' => 'https://schema.org', '@type' => 'PetStore',
  'name' => $CFG['site_name'], 'description' => $CFG['site_slogan'],
  'url' => url(''), 'telephone' => $CFG['phone'],
  'address' => ['@type' => 'PostalAddress', 'addressLocality' => $CFG['address'],
                'addressCountry' => 'IR'],
];
require __DIR__ . '/inc/header.php';
?>

<section class="hero">
  <div class="dust" aria-hidden="true"></div>
  <span class="blob f1" style="width:190px;height:190px;top:8%;right:6%;background:#fff;opacity:.45"></span>
  <span class="blob f3" style="width:130px;height:130px;bottom:12%;left:9%;background:var(--pink-mid);opacity:.35"></span>
  <span class="blob f2" style="width:80px;height:80px;top:22%;left:24%;background:var(--blue-mid);opacity:.4"></span>

  <div class="wrap">
    <div class="herocopy">
      <span class="kicker">پت‌شاپ بانی‌پت</span>
      <h1 class="h1"><span class="b">هر چی</span> <span class="p">لازم داره</span></h1>
      <p>غذا، بهداشت، لانه و لوازم همه حیوون‌های خونگی؛ در کنارش سرپرستی خرگوش، گربه، پرنده و جونده با راهنمایی کامل.</p>
      <div class="btns">
        <a class="btn" href="<?= url('shop.php') ?>">فروشگاه لوازم</a>
        <a class="btn ghost" href="<?= url('adopt.php') ?>">سرپرستی حیوان</a>
      </div>
    </div>
    <div class="heroart">
      <span class="bunnyglow" aria-hidden="true"></span>
      <span class="bunnyshadow" aria-hidden="true"></span>
      <img class="bunny" src="<?= url('assets/img/rabbit.webp') ?>" width="780" height="534"
           alt="خرگوش بانی‌پت" fetchpriority="high">
    </div>
  </div>
  <div class="dots" aria-hidden="true">
    <span class="dot on"></span><span class="dot"></span><span class="dot"></span>
  </div>
  <svg class="wave" viewBox="0 0 1440 70" preserveAspectRatio="none" aria-hidden="true">
    <path fill="#ffffff" d="M0 30c180-30 360-34 540-18s360 44 540 30 240-30 360-40v68H0z"/>
  </svg>
</section>

<section class="stats">
  <div class="wrap">
    <div class="stat reveal"><b>۶ خانواده</b><span>خرگوش، گربه، سگ، پرنده، جونده، آبزی</span></div>
    <div class="stat reveal"><b>۱۰ دسته</b><span>از غذا تا آکواریوم</span></div>
    <div class="stat reveal"><b>سرپرستی</b><span>با راهنمایی قبل و بعدش</span></div>
    <div class="stat reveal"><b>تحویل حضوری</b><span>حیوون زنده هیچ‌وقت با پست</span></div>
  </div>
</section>

<!-- لایه ۱ — خانواده حیوون -->
<section class="cats">
  <div class="wrap">
    <div class="sechead reveal">
      <h2 class="h2">حیوونت کیه</h2>
      <p>روی خانواده‌ش بزن تا لوازم، حیوون‌های آماده سرپرستی و مقاله‌هاش رو یکجا ببینی</p>
    </div>
    <div class="catgrid" style="--cols:6">
      <?php foreach ($pets as $p): ?>
        <a class="cat reveal <?= e($p['tone']) ?>" href="<?= url('pet.php?slug=' . urlencode($p['slug'])) ?>">
          <span class="circle"><?= e($p['emoji']) ?></span>
          <b><?= e($p['title']) ?></b>
          <small><?= e($p['subtitle']) ?></small>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- سرپرستی -->
<section class="products">
  <div class="dust" aria-hidden="true"></div>
  <svg class="wave" viewBox="0 0 1440 70" preserveAspectRatio="none" aria-hidden="true"
       style="margin-top:-1px;position:relative;z-index:2">
    <path fill="#ffffff" d="M0 0h1440v18c-220 30-460 26-720 6S240 6 0 30z"/>
  </svg>
  <div class="wrap">
    <div class="sechead reveal">
      <h2 class="h2">آماده سرپرستی</h2>
      <p>هر کدوم یه شخصیت داره. اول آشنا شو، بعد تصمیم بگیر</p>
    </div>
    <div class="prodgrid">
      <?php foreach ($anims as $a): ?>
        <div class="card reveal">
          <span class="tag <?= $a['tone'] === 'blue' ? 'blue' : '' ?>">آماده سرپرستی</span>
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
    <div style="text-align:center;margin-top:26px" class="reveal">
      <a class="btn ghost" href="<?= url('adopt.php') ?>">همه حیوون‌های آماده سرپرستی</a>
    </div>
  </div>
</section>

<!-- لایه ۲ — دسته لوازم -->
<section class="cats" style="padding-top:40px">
  <div class="wrap">
    <div class="sechead reveal">
      <h2 class="h2">دسته‌های فروشگاه</h2>
      <p>ده دسته لوازم، برای همه حیوون‌ها</p>
    </div>
    <div class="catgrid">
      <?php foreach ($cats as $c): ?>
        <a class="cat reveal <?= e($c['tone']) ?>" href="<?= url('shop.php?cat=' . urlencode($c['slug'])) ?>">
          <span class="circle"><?= e($c['emoji']) ?></span>
          <b><?= e($c['title']) ?></b>
          <small><?= e($c['subtitle']) ?></small>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="promos">
  <div class="wrap">
    <div class="promogrid">
      <a class="promo a reveal" href="<?= url('blog.php') ?>">
        <h3>مجله بانی‌پت</h3>
        <p>هفت راهنمای کاربردی نگهداری</p>
        <span class="go">مقاله‌ها ←</span><span class="emoji" aria-hidden="true">📚</span>
      </a>
      <a class="promo b reveal" href="<?= url('faq.php') ?>">
        <h3>مراحل سرپرستی</h3>
        <p>از درخواست تا تحویل، چهار قدم روشن</p>
        <span class="go">ببین ←</span><span class="emoji" aria-hidden="true">🐾</span>
      </a>
      <a class="promo c reveal" href="#join">
        <h3>مشاوره رایگان</h3>
        <p>قبل از هر تصمیمی یه گفت‌وگوی کوتاه</p>
        <span class="go">شروع ←</span><span class="emoji" aria-hidden="true">💬</span>
      </a>
    </div>
  </div>
</section>

<section class="products" style="background:linear-gradient(180deg,var(--pink-wash),var(--blue-wash))">
  <div class="dust" aria-hidden="true"></div>
  <div class="wrap" style="padding-top:34px">
    <div class="sechead reveal">
      <h2 class="h2">پرفروش‌های این هفته</h2>
      <p>چیزهایی که مشتری‌ها بیشتر دوباره سفارش می‌دن</p>
    </div>
    <div class="prodgrid">
      <?php foreach ($best as $p): ?>
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
    <div style="text-align:center;margin-top:26px" class="reveal">
      <a class="btn ghost" href="<?= url('shop.php') ?>">همه محصول‌ها</a>
    </div>
  </div>
</section>

<section class="mag">
  <div class="wrap">
    <div class="sechead reveal">
      <h2 class="h2">تازه‌های مجله</h2>
      <p>چیزهایی که کاش قبل از اولین حیوون خونگی می‌دونستیم</p>
    </div>
    <div class="maggrid">
      <?php foreach ($posts as $a): ?>
        <a class="post reveal" href="<?= url('article.php?slug=' . urlencode($a['slug'])) ?>">
          <div class="cover <?= e($a['tone']) ?>" aria-hidden="true"><?= $a['tone'] === 'blue' ? '🐾' : '🐰' ?></div>
          <div class="body">
            <div class="meta"><b><?= e($a['tag']) ?></b><span><?= fa_num($a['read_min']) ?> دقیقه خواندن</span></div>
            <h3><?= e($a['title']) ?></h3>
            <p><?= e(excerpt($a['excerpt'], 95)) ?></p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:24px" class="reveal">
      <a class="btn blue" href="<?= url('blog.php') ?>">همه مقاله‌ها</a>
    </div>
  </div>
</section>

<section class="band" id="join">
  <div class="dust" aria-hidden="true"></div>
  <svg class="wave" viewBox="0 0 1440 60" preserveAspectRatio="none" aria-hidden="true"
       style="position:absolute;top:0;left:0;z-index:2">
    <path fill="#ffffff" d="M0 0h1440v14c-240 30-480 26-720 8S220 4 0 26z"/>
  </svg>
  <div class="wrap">
    <div class="reveal">
      <h2 class="h2">اول حرف بزنیم، بعد تصمیم</h2>
      <p>شماره‌ت رو بذار، یه تماس کوتاه می‌گیریم و می‌گیم برای حیوونت واقعاً چی لازمه و چی لازم نیست. رایگانه و اصراری هم نداریم.</p>
      <ul class="tick" style="margin-top:16px">
        <li>مشاوره قبل از سرپرستی و قبل از خرید</li>
        <li>پیگیری روز دوم و روز هفتم بعد از تحویل</li>
        <li>شماره‌ت پیش خودمون می‌مونه</li>
      </ul>
    </div>
    <form class="joinbox" method="post" action="<?= url('index.php') ?>" data-once>
      <?= csrf_field() ?>
      <input type="hidden" name="join" value="1">
      <h3>درخواست مشاوره</h3>
      <small>یکی دو روزه باهات تماس می‌گیریم</small>
      <div class="field"><label for="jn">اسمت</label>
        <input id="jn" name="name" required maxlength="60" placeholder="مثلاً مهدی"></div>
      <div class="field"><label for="jp">شماره موبایل</label>
        <input id="jp" name="phone" required inputmode="tel" maxlength="15" placeholder="۰۹۱۲۳۴۵۶۷۸۹"></div>
      <div class="field"><label for="jt">درباره کدوم حیوون</label>
        <select id="jt" name="pet_type">
          <?php foreach ($pets as $p): ?>
            <option value="<?= e($p['title']) ?>"><?= e($p['title']) ?></option>
          <?php endforeach; ?>
          <option value="هنوز نمی‌دونم">هنوز نمی‌دونم</option>
        </select></div>
      <button class="btn wide" type="submit">ثبت درخواست</button>
    </form>
  </div>
</section>

<?php require __DIR__ . '/inc/footer.php'; ?>
