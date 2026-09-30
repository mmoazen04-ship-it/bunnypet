<?php
/* ==========================================================================
   نصب بانی‌پت — ساختار چندلایه
   لایه ۱: خانواده حیوون (خرگوش، گربه، سگ، …)
   لایه ۲: دسته لوازم (غذا، بهداشت، لانه، …)
   لایه ۳: خود محصول / خود حیوون
   یک بار توی مرورگر باز کن، بعد این فایل رو پاک کن.
   ========================================================================== */
require_once __DIR__ . '/inc/functions.php';
require_once __DIR__ . '/inc/seed-guide.php';

$done = [];
$err  = null;
$sqlite = db_is_sqlite();
$ID  = $sqlite ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';
$TXT = $sqlite ? 'TEXT' : 'LONGTEXT';
$SUF = $sqlite ? '' : ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';

try {
  $pdo = db();

  $pdo->exec("CREATE TABLE IF NOT EXISTS users (
    id $ID, name VARCHAR(100) NOT NULL, phone VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(150), pass_hash VARCHAR(255) NOT NULL, pet_type VARCHAR(40),
    role VARCHAR(20) NOT NULL DEFAULT 'user', created_at VARCHAR(25))$SUF");

  /* لایه ۱ — خانواده حیوون */
  $pdo->exec("CREATE TABLE IF NOT EXISTS pets (
    id $ID, slug VARCHAR(60) NOT NULL UNIQUE, title VARCHAR(100) NOT NULL,
    subtitle VARCHAR(160), intro $TXT, emoji VARCHAR(10), tone VARCHAR(10) DEFAULT 'pink',
    adopt INT DEFAULT 0, sort_order INT DEFAULT 0)$SUF");

  /* لایه ۲ — دسته لوازم */
  $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
    id $ID, slug VARCHAR(60) NOT NULL UNIQUE, title VARCHAR(100) NOT NULL,
    subtitle VARCHAR(160), tone VARCHAR(10) DEFAULT 'pink', emoji VARCHAR(10),
    sort_order INT DEFAULT 0)$SUF");

  /* لایه ۳ — محصول */
  $pdo->exec("CREATE TABLE IF NOT EXISTS products (
    id $ID, cat_id INT NOT NULL, pet_id INT, slug VARCHAR(80) NOT NULL UNIQUE,
    title VARCHAR(150) NOT NULL, short VARCHAR(255), body $TXT,
    price INT NOT NULL DEFAULT 0, old_price INT DEFAULT 0, badge VARCHAR(30),
    stock INT DEFAULT 10, tone VARCHAR(10) DEFAULT 'pink', img VARCHAR(200),
    active INT DEFAULT 1, created_at VARCHAR(25))$SUF");

  /* لایه ۳ — حیوون آماده سرپرستی */
  $pdo->exec("CREATE TABLE IF NOT EXISTS animals (
    id $ID, pet_id INT NOT NULL, slug VARCHAR(80) NOT NULL UNIQUE,
    name VARCHAR(80) NOT NULL, breed VARCHAR(80), age VARCHAR(60), sex VARCHAR(20),
    worth INT DEFAULT 0, story $TXT, care $TXT, status VARCHAR(30) DEFAULT 'ready',
    tone VARCHAR(10) DEFAULT 'pink', active INT DEFAULT 1, created_at VARCHAR(25))$SUF");

  $pdo->exec("CREATE TABLE IF NOT EXISTS articles (
    id $ID, slug VARCHAR(90) NOT NULL UNIQUE, title VARCHAR(200) NOT NULL,
    tag VARCHAR(50), pet_id INT, excerpt VARCHAR(400), body $TXT,
    read_min INT DEFAULT 4, tone VARCHAR(10) DEFAULT 'pink', views INT DEFAULT 0,
    active INT DEFAULT 1, created_at VARCHAR(25))$SUF");

  $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
    id $ID, code VARCHAR(30) NOT NULL UNIQUE, user_id INT, name VARCHAR(100),
    phone VARCHAR(20), address $TXT, note $TXT, items_total INT DEFAULT 0,
    shipping INT DEFAULT 0, total INT DEFAULT 0, gateway VARCHAR(30),
    status VARCHAR(30) DEFAULT 'pending', ref_id VARCHAR(80), created_at VARCHAR(25))$SUF");

  $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
    id $ID, order_id INT NOT NULL, product_id INT, title VARCHAR(150),
    price INT, qty INT)$SUF");

  /* درخواست سرپرستی */
  $pdo->exec("CREATE TABLE IF NOT EXISTS adoptions (
    id $ID, code VARCHAR(30), animal_id INT, user_id INT, name VARCHAR(100),
    phone VARCHAR(20), city VARCHAR(80), home VARCHAR(120), experience $TXT,
    status VARCHAR(30) DEFAULT 'new', created_at VARCHAR(25))$SUF");

  $pdo->exec("CREATE TABLE IF NOT EXISTS leads (
    id $ID, name VARCHAR(100), phone VARCHAR(20), pet_type VARCHAR(40),
    message $TXT, source VARCHAR(40), created_at VARCHAR(25))$SUF");

  $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
    id $ID, ip VARCHAR(50), k VARCHAR(80), at VARCHAR(25))$SUF");

  $done[] = 'جدول‌های دیتابیس ساخته شد';

  /* ---------------- لایه ۱: خانواده حیوون ---------------- */
  $pets = [
    ['khargoosh','خرگوش','مینی‌لوپ و نژادهای کوچک','pink','🐰',1,
     'خرگوش آروم‌ترین هم‌خونه‌ایه که می‌تونی داشته باشی، به شرطی که بدونی چی لازم داره. از انتخاب تا ماه‌های بعدش کنارتیم.'],
    ['gorbe','گربه','از بچه‌گربه تا بزرگسال','blue','🐱',1,
     'گربه‌ها هر کدوم شخصیت خودشون رو دارن. کمکت می‌کنیم اونی رو انتخاب کنی که به زندگی تو می‌خوره، نه فقط اونی که خوشگل‌تره.'],
    ['sag','سگ','لوازم و مراقبت','pink','🐶',0,
     'برای سگ‌ها لوازم نگهداری داریم؛ غذا، قلاده، بهداشت و مراقبت.'],
    ['parande','پرنده','قناری، عروس هلندی، طوطی','blue','🐦',1,
     'پرنده‌ها به فضا و آرامش حساسن. قبل از انتخاب، درباره صدا و نور و جای قفس باهات حرف می‌زنیم.'],
    ['jooande','جونده','همستر، خوکچه هندی','pink','🐹',1,
     'جونده‌ها برای شروع عالی‌ان، ولی هر کدوم قانون خودشون رو دارن. راهنماییت می‌کنیم.'],
    ['abzi','آبزی','ماهی و آکواریوم','blue','🐠',0,
     'آکواریوم قبل از ماهی باید آماده بشه. تجهیزات و راهنماییش با ما.'],
  ];
  if (!one('SELECT id FROM pets LIMIT 1')) {
    $i = 0;
    foreach ($pets as $p) {
      q('INSERT INTO pets (slug,title,subtitle,tone,emoji,adopt,intro,sort_order)
         VALUES (?,?,?,?,?,?,?,?)',
        [$p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[6], $i++]);
    }
    $done[] = fa_num(count($pets)) . ' خانواده حیوون اضافه شد';
  }
  $pid = [];
  foreach (all('SELECT id, slug FROM pets') as $r) { $pid[$r['slug']] = $r['id']; }

  /* ---------------- لایه ۲: دسته لوازم ---------------- */
  $cats = [
    ['ghaza','غذا و تشویقی','خشک، تر، یونجه، تنقلات','pink','🥣'],
    ['behdasht','بهداشت و نظافت','شامپو، برس، پد، دستمال','blue','🧼'],
    ['khak','خاک و بستر','خاک دستشویی، پوشال، بستر','pink','🪵'],
    ['lane','لانه و خواب','قفس، تشک، جای خواب','blue','🏠'],
    ['haml','حمل و نقل','باکس، کوله، قلاده','pink','🎒'],
    ['bazi','اسباب‌بازی','تونل، توپ، اسکرچر','blue','🧸'],
    ['zarf','ظرف و آبخوری','ظرف غذا، آبخوری، غذاخوری خودکار','pink','🥛'],
    ['darman','مراقبت و درمان','مکمل، ضدانگل، کمک‌های اولیه','blue','💊'],
    ['pooshak','قلاده و لباس','قلاده، هارنس، لباس','pink','🧣'],
    ['akvarium','آکواریوم و تجهیزات','تنگ، فیلتر، بخاری، تور','blue','🌊'],
  ];
  if (!one('SELECT id FROM categories LIMIT 1')) {
    $i = 0;
    foreach ($cats as $c) {
      q('INSERT INTO categories (slug,title,subtitle,tone,emoji,sort_order) VALUES (?,?,?,?,?,?)',
        [$c[0], $c[1], $c[2], $c[3], $c[4], $i++]);
    }
    $done[] = fa_num(count($cats)) . ' دسته لوازم اضافه شد';
  }
  $cid = [];
  foreach (all('SELECT id, slug FROM categories') as $r) { $cid[$r['slug']] = $r['id']; }

  /* ---------------- لایه ۳: محصول ---------------- */
  $prods = [
    ['ghaza','khargoosh','yonje-alfalfa','یونجه آلفالفا تازه — یک کیلو','غذای اصلی خرگوش، نه تنقلات',
     'یونجه باید همیشه در دسترس باشه. تازه بسته‌بندی می‌شه و بوی کپک نمی‌ده.',185000,0,'پرفروش',40,'pink'],
    ['ghaza','khargoosh','pellet-khargoosh','پلت مخصوص خرگوش بالغ','بدون دانه‌های رنگی',
     'دانه‌های رنگی خرگوش رو بدغذا می‌کنن. این پلت یکدسته.',320000,0,'',24,'blue'],
    ['ghaza','gorbe','ghaza-khoshk-gorbe','غذای خشک گربه بالغ — دو کیلو','مناسب گربه خانگی کم‌تحرک',
     'برای گربه‌هایی که بیشتر وقتشون توی خونه‌ست فرموله شده.',690000,0,'',25,'blue'],
    ['ghaza','gorbe','tashvighi-gorbe','تشویقی نرم گربه','برای آموزش و دوست شدن',
     'تیکه‌های ریز نرم، مناسب آموزش.',145000,0,'',30,'pink'],
    ['ghaza','parande','daneh-ghanari','دانه مخلوط قناری','مخلوط تازه و الک‌شده',
     'بدون پوسته اضافه و خاک.',165000,0,'',18,'blue'],
    ['behdasht','khargoosh','shampoo-foomi','شامپو خشک فومی','بدون آب، برای حیوون‌های حساس',
     'خرگوش نباید حموم آب گرم بره. کف رو می‌مالی و با حوله خشک می‌کنی.',240000,280000,'تخفیف',30,'pink'],
    ['behdasht','gorbe','beres-porzgir','برس پرزگیر مخصوص','برای فصل ریزش مو',
     'دندونه‌های گرد که پوست رو زخم نمی‌کنه.',195000,0,'',18,'blue'],
    ['behdasht','sag','shampoo-sag','شامپو سگ ضدحساسیت','بدون سولفات',
     'برای پوست‌های حساس و خارش فصلی.',380000,0,'',12,'pink'],
    ['khak','khargoosh','khak-dastshooi','خاک دستشویی بدون گرد','کم‌گرد، جذب بالا، بوگیر',
     'گرد کم یعنی ریه حیوون و خودت اذیت نمی‌شه.',320000,0,'',22,'blue'],
    ['khak','jooande','pooshal-jooande','پوشال بستر جونده','چوب طبیعی بدون رنگ',
     'جونده بستر رو می‌جوه، پس رنگ و چسب شیمیایی نداره.',180000,0,'',20,'pink'],
    ['lane','khargoosh','lane-khargoosh','لانه چوبی خرگوش','دو ورودی، چوب بی‌خطر',
     'دو ورودی داره که خرگوش حس گیر افتادن نگیره.',890000,0,'',7,'blue'],
    ['lane','gorbe','jaye-khab-makhmali','جای خواب مخملی','نرم، قابل شستشو، دو سایز',
     'کف ضدلغزش داره و کامل شسته می‌شه.',540000,620000,'تخفیف',14,'pink'],
    ['lane','parande','ghafas-ghanari','قفس قناری دوطبقه','با ظرف و چوب‌نشیمن',
     'میله‌ها با فاصله استاندارد و رنگ بی‌خطر.',1150000,0,'',6,'blue'],
    ['haml','gorbe','box-haml','باکس حمل حیوان','درب فلزی، قفل ایمن',
     'برای رفتن به دامپزشکی. مناسب خرگوش و گربه.',780000,0,'پرفروش',9,'pink'],
    ['haml','sag','ghalade-narm','قلاده نرم سگ','بالشتک‌دار، ضد فشار روی گردن',
     'پهن و نرمه که موقع کشیدن فشار نیاره.',450000,0,'',13,'blue'],
    ['bazi','khargoosh','tonel-parche-i','تونل پارچه‌ای بازی','جمع‌شو، قابل شستشو',
     'خرگوش و گربه هر دو عاشق قایم شدن توی تونلن.',380000,0,'',16,'pink'],
    ['bazi','gorbe','toop-tashvighi','توپ تشویقی هوشمند','غذا رو کم‌کم می‌ریزه بیرون',
     'حیوون باید تلاش کنه تا غذا بریزه؛ هم سرگرم می‌شه هم کند غذا می‌خوره.',290000,0,'',20,'blue'],
    ['zarf','khargoosh','zarf-ghaza-dogholoo','ظرف غذا و آب دوقلو','سرامیکی، سنگین، برنمی‌گرده',
     'سرامیک سنگینه و حیوون نمی‌تونه چپه‌ش کنه.',150000,0,'پرفروش',35,'pink'],
    ['zarf','gorbe','abkhori-gardeshi','آبخوری گردشی گربه','آب در جریان، کم‌صدا',
     'گربه‌ها آب در جریان رو بیشتر می‌خورن.',980000,0,'',8,'blue'],
    ['darman','khargoosh','mokammel-vitamin','مکمل ویتامین عمومی','قطره خوراکی، یک ماه مصرف',
     'قبل از مصرف با دامپزشکت هماهنگ کن.',260000,0,'',15,'pink'],
    ['darman','sag','zed-angal','قطره ضدانگل خارجی','برای سگ و گربه بالغ',
     'دوز بر اساس وزن حیوون انتخاب می‌شه.',420000,0,'',11,'blue'],
    ['pooshak','sag','harness-sag','هارنس سینه‌ای سگ','قابل تنظیم، سه سایز',
     'فشار رو روی سینه پخش می‌کنه نه گردن.',560000,0,'',10,'pink'],
    ['akvarium','abzi','filter-akvarium','فیلتر آکواریوم کم‌صدا','مناسب تا ۶۰ لیتر',
     'شب که خوابی صداش اذیتت نمی‌کنه.',660000,0,'',8,'blue'],
    ['akvarium','abzi','bokhari-akvarium','بخاری آکواریوم ترموستاتیک','تنظیم دما، محافظ شکست',
     'دما رو ثابت نگه می‌داره.',540000,0,'',9,'pink'],
  ];
  if (!one('SELECT id FROM products LIMIT 1')) {
    foreach ($prods as $p) {
      q('INSERT INTO products (cat_id,pet_id,slug,title,short,body,price,old_price,badge,stock,tone,active,created_at)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,1,?)',
        [$cid[$p[0]], $pid[$p[1]], $p[2], $p[3], $p[4], $p[5], $p[6], $p[7], $p[8], $p[9], $p[10],
         date('Y-m-d H:i:s')]);
    }
    $done[] = fa_num(count($prods)) . ' محصول اضافه شد';
  }

  /* ---------------- لایه ۳: حیوون‌های آماده سرپرستی ---------------- */
  $animals = [
    ['khargoosh','toofan','طوفان','مینی‌لوپ','۳ ماه','نر',3800000,'ready','pink',
     'طوفان اسمش رو از دویدن‌های شبونه‌ش گرفته. آدم‌ها رو زود قبول می‌کنه و از بغل بدش نمیاد.',
     'به فضای دویدن روزانه و یونجه همیشگی نیاز داره. هنوز عقیم نشده.'],
    ['khargoosh','pashmak','پشمک','مینی‌لوپ','۵ ماه','ماده',4200000,'ready','blue',
     'پشمک آروم‌تره، اول یه گوشه می‌شینه و نگاه می‌کنه. با صبر، رفیق خیلی خوبی می‌شه.',
     'دستشویی رو سر جاش یاد گرفته. برای خونه‌های ساکت مناسبه.'],
    ['khargoosh','noghl','نُقل','هلندی کوتوله','۴ ماه','نر',3200000,'reserved','pink',
     'نقل کنجکاوه و همه جا سرک می‌کشه. برای خونه‌ای که بچه داره گزینه خوبیه.',
     'فعلاً برای یه خانواده رزرو شده.'],
    ['gorbe','mishi','میشی','دورگه ایرانی','۲ ماه','ماده',2400000,'ready','blue',
     'میشی از یه کوچه پیدا شد؛ واکسن و آزمایشش انجام شده و الان کاملاً سالمه.',
     'واکسن سه‌گانه زده و انگل‌زدایی شده. باید داخل خونه نگه داشته بشه.'],
    ['gorbe','zoghal','زغال','دورگه مو کوتاه','۷ ماه','نر',2100000,'ready','pink',
     'زغال با سگ و بچه راه میاد و اصلاً ترسو نیست.',
     'عقیم شده و دستشویی‌ش کاملاً تمیزه.'],
    ['parande','limoo','لیمو','قناری','۶ ماه','نر',1900000,'ready','blue',
     'لیمو صبح‌ها می‌خونه و به نور حساسه. برای خونه‌های روشن عالیه.',
     'قفس باید دور از آشپزخونه و دود باشه.'],
    ['jooande','pesteh','پسته','خوکچه هندی','۳ ماه','ماده',1400000,'ready','pink',
     'پسته سروصدا دوست نداره، ولی وقتی بهت عادت کنه با دیدنت سوت می‌زنه.',
     'خوکچه هندی تنها نگه داشته نمی‌شه؛ بهتره جفت سرپرستی بشه.'],
    ['jooande','fandogh','فندق','همستر سوری','۲ ماه','نر',900000,'adopted','blue',
     'فندق ماه پیش سرپرست پیدا کرد و الان حالش خوبه.',
     'همستر سوری باید تنها نگه داشته بشه.'],
  ];
  if (!one('SELECT id FROM animals LIMIT 1')) {
    foreach ($animals as $a) {
      q('INSERT INTO animals (pet_id,slug,name,breed,age,sex,worth,status,tone,story,care,active,created_at)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,1,?)',
        [$pid[$a[0]], $a[1], $a[2], $a[3], $a[4], $a[5], $a[6], $a[7], $a[8], $a[9], $a[10],
         date('Y-m-d H:i:s')]);
    }
    $done[] = fa_num(count($animals)) . ' حیوون آماده سرپرستی اضافه شد';
  }

  /* ---------------- ۷ مقاله ---------------- */
  $arts = [
    ['check-list-rooz-aval','قبل از اینکه حیوون بیاری خونه، این ۷ چیز رو آماده کن','شروع','khargoosh',
     'هیجان روز اول باعث می‌شه نصف چیزها یادت بره. این فهرست رو بخون و خیالت راحت باشه.',5,'pink',
     '<p>آوردن یه حیوون به خونه شبیه آوردن یه بچه‌ست که حرف نمی‌زنه ولی همه چی رو می‌جوه. اگه از قبل آماده باشی، هفته اول برای هر دوتون خیلی راحت‌تر می‌گذره.</p>
      <h2>۱. یه فضای امن، نه لزوماً قفس بزرگ</h2><p>یه گوشه از اتاق که کفش لیز نباشه و سیم برق توش نباشه، از گرون‌ترین قفس بازار بهتره.</p>
      <h2>۲. غذای اصلی، به اندازه‌ای که فکر می‌کنی زیاده</h2><p>برای خرگوش یونجه، برای گربه غذای مخصوص سن خودش. تنقلات جای غذای اصلی رو نمی‌گیره.</p>
      <h2>۳. ظرف آب سنگین</h2><p>ظرف پلاستیکی سبک رو راحت چپه می‌کنه. سرامیکی بگیر.</p>
      <h2>۴. بستر و خاک مناسب</h2><p>خاک معطر و پرگرد برای ریه حیوون خوب نیست.</p>
      <h2>۵. چیزی برای جویدن یا چنگ زدن</h2><p>اگه ندی، سراغ مبل و سیم می‌ره.</p>
      <h2>۶. شماره یه دامپزشک</h2><p>قبل از اینکه لازمت بشه پیداش کن و ذخیره‌ش کن. همه دامپزشک‌ها با حیوون کوچیک کار نمی‌کنن.</p>
      <h2>۷. صبر</h2><p>هفته اول احتمالاً قایم می‌شه. این عادیه. دنبالش نکن، بذار خودش بیاد.</p>'],

    ['dastshooi-yad-dadan','دستشویی رفتن حیوون رو چطور سر جاش یاد بدیم','آموزش','khargoosh',
     'خرگوش و گربه هر دو سطل دستشویی یاد می‌گیرن. فقط روشش فرق داره.',4,'blue',
     '<p>حیوون‌ها ذاتاً دوست دارن یه گوشه ثابت رو انتخاب کنن. کار تو فقط اینه که اون گوشه رو تبدیل به سطل کنی.</p>
      <h2>اول ببین خودش کجا رو انتخاب کرده</h2><p>دو سه روز نگاهش کن، بعد سطل رو ببر همون‌جا؛ نه جایی که تو دوست داری.</p>
      <h2>برای خرگوش، توی سطل یونجه بذار</h2><p>خرگوش‌ها همزمان که غذا می‌خورن دستشویی می‌کنن.</p>
      <h2>خرابکاری بیرون سطل رو بردار و بنداز توش</h2><p>بوی خودش بهش می‌گه اینجا جای درستیه.</p>
      <h2>دعوا و تنبیه جواب نمی‌ده</h2><p>ربط بین دعوای تو و کاری که ده دقیقه قبل کرده رو نمی‌فهمه؛ فقط ازت می‌ترسه.</p>
      <h2>عقیم‌سازی تفاوت بزرگی می‌سازه</h2><p>خیلی از علامت‌گذاری‌ها هورمونیه.</p>
      <p>اگه بعد از مدت‌ها یهو بی‌نظم شد، یعنی یا استرس داره یا مریضه.</p>'],

    ['gorbe-va-khargoosh-ba-ham','گربه و خرگوش توی یه خونه؛ می‌شه یا نمی‌شه','زندگی مشترک','gorbe',
     'جواب کوتاه: می‌شه، ولی نه با عجله و نه با هر گربه‌ای.',6,'pink',
     '<p>خیلی خونه‌ها موفق شدن، ولی نه با روش «بذاریم ببینیم چی می‌شه».</p>
      <h2>خرگوش شکار حساب می‌شه</h2><p>حتی گربه آروم هم ممکنه با حرکت ناگهانی تحریک بشه. ترس خرگوش خودش به تنهایی خطرناکه.</p>
      <h2>مرحله اول: فقط بو</h2><p>یکی دو هفته دو فضای جدا. حوله هرکدوم رو بذار پیش اون یکی.</p>
      <h2>مرحله دوم: دیدن از پشت در</h2><p>در توری یا قفس وسط، چند روز، هر بار کوتاه.</p>
      <h2>مرحله سوم: ملاقات کوتاه زیر نظر</h2><p>پنج دقیقه، ناخن گربه کوتاه، و یه راه فرار برای خرگوش.</p>
      <h2>کی باید بی‌خیال شد</h2><p>اگه گربه حالت شکار می‌گیره یا خرگوش غذا نمی‌خوره، اصرار نکن.</p>'],

    ['zemestan-negahdari','زمستون تهران و حیوون خونگی؛ چند نکته ساده','فصلی','gorbe',
     'بخاری روشن و پنجره بسته برای ما راحته و برای اون‌ها لزوماً نه.',4,'blue',
     '<p>زمستون دو خطر داره که جدی گرفته نمی‌شه: هوای خشک و نبود تهویه.</p>
      <h2>قفس رو دم بخاری نذار</h2><p>گرمای مستقیم و نوسان دما بدتر از سرماست.</p>
      <h2>هوای خشک یعنی پوست خشک</h2><p>یه ظرف آب روی بخاری یا رطوبت‌ساز کوچیک بذار.</p>
      <h2>تهویه رو کامل قطع نکن</h2><p>روزی چند دقیقه پنجره یه اتاق دیگه رو باز کن، نه اینکه باد مستقیم بخوره بهش.</p>
      <h2>آب تازه</h2><p>زمستون کمتر آب می‌خورن و همین یبوست میاره.</p>
      <h2>کی ببریمش دامپزشک</h2><p>بی‌اشتهایی بیشتر از دوازده ساعت، خس‌خس، آبریزش بینی، کز کردن.</p>'],

    ['kharid-online-lavazem','چی رو آنلاین سفارش بدیم و چی رو از نزدیک ببینیم','راهنما','sag',
     'بعضی چیزها آنلاین منطقیه، بعضی‌ها باید از نزدیک اندازه بشه.',5,'pink',
     '<p>آنلاین سفارش دادن هم ارزون‌تر درمیاد هم وقت‌گیر نیست، ولی همه چیز رو نباید ندیده گرفت.</p>
      <h2>اینا رو راحت آنلاین بگیر</h2><ul><li>غذا، یونجه، خاک و بستر</li><li>شامپو و لوازم بهداشتی</li><li>تشویقی و اسباب‌بازی</li></ul>
      <h2>اینا رو از نزدیک ببین</h2><ul><li>قفس و باکس حمل؛ اندازه توی عکس گول‌زننده‌ست</li><li>قلاده و هارنس، که باید اندازه بدن باشه</li><li>خود حیوون</li></ul>
      <h2>قبل از ثبت سفارش سه چیز رو چک کن</h2><p>تاریخ انقضا، وزن دقیق بسته، و اینکه فروشنده بعدش جواب می‌ده یا نه.</p>
      <h2>حیوون زنده با پست جابه‌جا نمی‌شه</h2><p>ما هم به همین دلیل تحویل رو فقط حضوری انجام می‌دیم.</p>'],

    ['hazine-vagheie-negahdari','هزینه واقعی نگهداری یه حیوون خونگی چقدره','برنامه‌ریزی','khargoosh',
     'هزینه اصلی روز اول نیست؛ ماه‌های بعدشه. بیا صادقانه حساب کنیم.',6,'blue',
     '<p>بیشتر کسایی که حیوون رو پس می‌دن آدم‌های بدی نیستن؛ فقط حساب‌وکتاب ماه‌های بعد رو نکرده بودن.</p>
      <h2>هزینه‌های یک‌باره</h2><p>لانه یا قفس، ظرف، باکس حمل، بستر اولیه. اینا رو یه بار می‌دی.</p>
      <h2>هزینه‌های هر ماه</h2><p>غذا، خاک و بستر، تشویقی. این عددیه که باید توی بودجه ماهانه‌ت جا بشه.</p>
      <h2>هزینه‌هایی که یادت می‌ره</h2><p>واکسن، عقیم‌سازی، ضدانگل دوره‌ای، و یه مراجعه اورژانسی که بالاخره پیش میاد.</p>
      <h2>قانون ساده ما</h2><p>اگه هزینه ماهانه رو نمی‌تونی راحت بدی، هنوز وقتش نیست. این حرف، مشتری از دست دادنه ولی درسته.</p>
      <h2>چطور کمش کنیم</h2><p>خرید عمده غذا و بستر، و نخریدن وسایل تزئینی که حیوون اصلاً بهشون کاری نداره.</p>'],

    ['neshanehaye-bimari','۸ نشونه‌ای که یعنی حیوونت حالش خوب نیست','سلامت','gorbe',
     'حیوون‌ها درد رو قایم می‌کنن. تا وقتی علامت واضح بدن، معمولاً دیره.',5,'pink',
     '<p>در طبیعت، حیوونی که ضعف نشون بده شکار می‌شه. همین غریزه باعث می‌شه توی خونه هم دردش رو قایم کنه.</p>
      <h2>چیزهایی که باید جدی بگیری</h2>
      <ul><li>نخوردن غذا بیشتر از دوازده ساعت (برای خرگوش حتی کمتر)</li>
      <li>کم شدن یا قطع شدن دستشویی</li><li>کز کردن توی گوشه و بی‌حرکت موندن</li>
      <li>خس‌خس یا آبریزش بینی و چشم</li><li>دندون قروچه یا صدای درد</li>
      <li>لنگیدن یا نرفتن روی یه پا</li><li>ریزش مو یا خاروندن مداوم یه نقطه</li>
      <li>تغییر ناگهانی رفتار؛ حیوون اجتماعی که یهو پنهون می‌شه</li></ul>
      <h2>خودسرانه دارو نده</h2><p>خیلی از داروهای انسانی برای حیوون‌های کوچیک سمّیه.</p>
      <h2>قبل از رفتن دامپزشک</h2><p>یادداشت کن از کی شروع شده، چقدر غذا خورده و دستشویی‌ش چه شکلی بوده. همین یادداشت کوتاه کار دامپزشک رو راحت می‌کنه.</p>'],
  ];
  if (!one('SELECT id FROM articles LIMIT 1')) {
    $d = time();
    foreach ($arts as $a) {
      q('INSERT INTO articles (slug,title,tag,pet_id,excerpt,read_min,tone,body,views,active,created_at)
         VALUES (?,?,?,?,?,?,?,?,?,1,?)',
        [$a[0], $a[1], $a[2], isset($pid[$a[3]]) ? $pid[$a[3]] : null, $a[4], $a[5], $a[6], $a[7],
         rand(120, 900), date('Y-m-d H:i:s', $d)]);
      $d -= 86400 * 5;
    }
    $done[] = fa_num(count($arts)) . ' مقاله اضافه شد';
  }

  /* ---------------- راهنمای نگهداری ---------------- */
  $n = seed_guide($ID, $TXT, $SUF);
  if ($n) { $done[] = fa_num($n) . ' بخش راهنمای نگهداری خرگوش اضافه شد'; }

  /* ---------------- مدیر ---------------- */
  $adminPass = null;
  if (!one("SELECT id FROM users WHERE role = 'admin'")) {
    $adminPass = 'bunny' . rand(1000, 9999);
    q('INSERT INTO users (name,phone,email,pass_hash,role,created_at) VALUES (?,?,?,?,?,?)',
      ['مدیر سایت', '09120000000', 'admin@bunnypet.ir',
       password_hash($adminPass, PASSWORD_DEFAULT), 'admin', date('Y-m-d H:i:s')]);
    $done[] = 'حساب مدیر ساخته شد';
  }

} catch (Exception $ex) {
  $err = $ex->getMessage();
}
?><!DOCTYPE html>
<html lang="fa" dir="rtl"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>نصب بانی‌پت</title><link rel="stylesheet" href="assets/css/style.css">
</head><body class="plain">
<div class="authwrap" style="max-width:660px"><div class="authcard">
  <h1 class="h2">نصب بانی‌پت</h1>
  <?php if ($err): ?>
    <div class="note err">خطا: <?= e($err) ?></div>
  <?php else: ?>
    <ul class="tick">
      <?php foreach ($done as $d): ?><li><?= e($d) ?></li><?php endforeach; ?>
      <?php if (!$done): ?><li>همه چیز از قبل نصب بود</li><?php endif; ?>
    </ul>
    <?php if (!empty($adminPass)): ?>
      <div class="note warn"><b>این دو خط رو همین الان یادداشت کن:</b><br>
        شماره ورود مدیر: <code>09120000000</code><br>
        رمز مدیر: <code><?= e($adminPass) ?></code><br>
        بعد از اولین ورود، رمز رو عوض کن.</div>
    <?php endif; ?>
    <div class="note err"><b>مرحله آخر:</b> فایل <code>install.php</code> رو از روی هاست پاک کن.</div>
    <a class="btn" href="index.php">رفتن به سایت</a>
    <a class="btn ghost" href="admin/index.php">پنل مدیریت</a>
  <?php endif; ?>
</div></div>
</body></html>
