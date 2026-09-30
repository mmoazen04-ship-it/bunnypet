<?php
/* ==========================================================================
   پنل مدیریت بانی‌پت
   ورود با شماره و رمز مدیر از صفحه login.php سایت
   ========================================================================== */
require_once __DIR__ . '/../inc/functions.php';
require_admin();

$tab = isset($_GET['tab']) ? clean($_GET['tab'], 20) : 'home';
$msg = '';

/* ---------------- عملیات ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();

  if (isset($_POST['save_product'])) {
    $id = (int)$_POST['id'];
    $d = [
      'cat_id' => (int)$_POST['cat_id'],
      'pet_id' => (int)$_POST['pet_id'],
      'title'  => clean($_POST['title'], 150),
      'slug'   => clean($_POST['slug'], 80),
      'short'  => clean($_POST['short'], 250),
      'body'   => clean($_POST['body'], 4000),
      'price'  => (int)en_num($_POST['price']),
      'old_price' => (int)en_num($_POST['old_price']),
      'badge'  => clean($_POST['badge'], 30),
      'stock'  => (int)en_num($_POST['stock']),
      'tone'   => $_POST['tone'] === 'blue' ? 'blue' : 'pink',
      'active' => isset($_POST['active']) ? 1 : 0,
    ];
    if ($d['slug'] === '') { $d['slug'] = 'p-' . time(); }
    if ($id) {
      q('UPDATE products SET cat_id=?,pet_id=?,title=?,slug=?,short=?,body=?,price=?,old_price=?,badge=?,
         stock=?,tone=?,active=? WHERE id=?',
        array_merge(array_values($d), [$id]));
      $msg = 'محصول به‌روز شد';
    } else {
      q('INSERT INTO products (cat_id,pet_id,title,slug,short,body,price,old_price,badge,stock,tone,active,created_at)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)',
        array_merge(array_values($d), [date('Y-m-d H:i:s')]));
      $msg = 'محصول اضافه شد';
    }
    $tab = 'products';
  }

  if (isset($_POST['del_product'])) {
    q('DELETE FROM products WHERE id = ?', [(int)$_POST['del_product']]);
    $msg = 'محصول حذف شد'; $tab = 'products';
  }

  if (isset($_POST['save_article'])) {
    $id = (int)$_POST['id'];
    $d = [
      'title'    => clean($_POST['title'], 200),
      'slug'     => clean($_POST['slug'], 90),
      'tag'      => clean($_POST['tag'], 50),
      'excerpt'  => clean($_POST['excerpt'], 380),
      'body'     => $_POST['body'],  /* فقط مدیر می‌نویسه؛ HTML مجازه */
      'read_min' => max(1, (int)en_num($_POST['read_min'])),
      'tone'     => $_POST['tone'] === 'blue' ? 'blue' : 'pink',
      'active'   => isset($_POST['active']) ? 1 : 0,
    ];
    if ($d['slug'] === '') { $d['slug'] = 'a-' . time(); }
    if ($id) {
      q('UPDATE articles SET title=?,slug=?,tag=?,excerpt=?,body=?,read_min=?,tone=?,active=? WHERE id=?',
        array_merge(array_values($d), [$id]));
      $msg = 'مقاله به‌روز شد';
    } else {
      q('INSERT INTO articles (title,slug,tag,excerpt,body,read_min,tone,active,views,created_at)
         VALUES (?,?,?,?,?,?,?,?,0,?)',
        array_merge(array_values($d), [date('Y-m-d H:i:s')]));
      $msg = 'مقاله اضافه شد';
    }
    $tab = 'articles';
  }

  if (isset($_POST['del_article'])) {
    q('DELETE FROM articles WHERE id = ?', [(int)$_POST['del_article']]);
    $msg = 'مقاله حذف شد'; $tab = 'articles';
  }

  if (isset($_POST['save_animal'])) {
    $id = (int)$_POST['id'];
    $d = [
      'pet_id' => (int)$_POST['pet_id'],
      'name'   => clean($_POST['name'], 80),
      'slug'   => clean($_POST['slug'], 80),
      'breed'  => clean($_POST['breed'], 80),
      'age'    => clean($_POST['age'], 60),
      'sex'    => clean($_POST['sex'], 20),
      'worth'  => (int)en_num($_POST['worth']),
      'story'  => clean($_POST['story'], 1500),
      'care'   => clean($_POST['care'], 800),
      'status' => in_array($_POST['status'], ['ready','reserved','adopted'], true) ? $_POST['status'] : 'ready',
      'tone'   => $_POST['tone'] === 'blue' ? 'blue' : 'pink',
      'active' => isset($_POST['active']) ? 1 : 0,
    ];
    if ($d['slug'] === '') { $d['slug'] = 'a-' . time(); }
    if ($id) {
      q('UPDATE animals SET pet_id=?,name=?,slug=?,breed=?,age=?,sex=?,worth=?,story=?,care=?,
         status=?,tone=?,active=? WHERE id=?', array_merge(array_values($d), [$id]));
      $msg = 'پرونده حیوون به‌روز شد';
    } else {
      q('INSERT INTO animals (pet_id,name,slug,breed,age,sex,worth,story,care,status,tone,active,created_at)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)', array_merge(array_values($d), [date('Y-m-d H:i:s')]));
      $msg = 'حیوون اضافه شد';
    }
    $tab = 'animals';
  }

  if (isset($_POST['del_animal'])) {
    q('DELETE FROM animals WHERE id = ?', [(int)$_POST['del_animal']]);
    $msg = 'پرونده حذف شد'; $tab = 'animals';
  }

  if (isset($_POST['adoption_status'])) {
    $ok = ['new','called','approved','rejected','done'];
    $st = in_array($_POST['status'], $ok, true) ? $_POST['status'] : 'new';
    q('UPDATE adoptions SET status = ? WHERE id = ?', [$st, (int)$_POST['adoption_status']]);
    $msg = 'وضعیت درخواست عوض شد'; $tab = 'adoptions';
  }

  if (isset($_POST['save_gsec'])) {
    $id = (int)$_POST['id'];
    q('UPDATE guide_sections SET title=?, slug=?, body=?, do_tip=?, dont_tip=?, danger_tip=?,
       img=?, video=?, active=? WHERE id=?',
      [clean($_POST['title'], 200), clean($_POST['slug'], 90), clean($_POST['body'], 2000),
       clean($_POST['do_tip'], 300), clean($_POST['dont_tip'], 300),
       clean($_POST['danger_tip'], 300), clean($_POST['img'], 200),
       clean($_POST['video'], 300), isset($_POST['active']) ? 1 : 0, $id]);
    $msg = 'بخش راهنما به‌روز شد'; $tab = 'guide';
  }

  if (isset($_POST['order_status'])) {
    $ok = ['pending', 'awaiting', 'paid', 'sent', 'failed', 'canceled'];
    $st = in_array($_POST['status'], $ok, true) ? $_POST['status'] : 'pending';
    q('UPDATE orders SET status = ? WHERE id = ?', [$st, (int)$_POST['order_status']]);
    $msg = 'وضعیت سفارش عوض شد'; $tab = 'orders';
  }
}

$cats = all('SELECT * FROM categories ORDER BY sort_order');
$petsAll = all('SELECT * FROM pets ORDER BY sort_order');
$edit = null;
if ($tab === 'products' && isset($_GET['edit'])) {
  $edit = one('SELECT * FROM products WHERE id = ?', [(int)$_GET['edit']]);
}
if ($tab === 'animals' && isset($_GET['edit'])) {
  $edit = one('SELECT * FROM animals WHERE id = ?', [(int)$_GET['edit']]);
}
if ($tab === 'guide' && isset($_GET['edit'])) {
  $edit = one('SELECT * FROM guide_sections WHERE id = ?', [(int)$_GET['edit']]);
}
if ($tab === 'articles' && isset($_GET['edit'])) {
  $edit = one('SELECT * FROM articles WHERE id = ?', [(int)$_GET['edit']]);
}
$lbl = ['paid' => 'پرداخت شده', 'failed' => 'ناموفق', 'awaiting' => 'در انتظار پرداخت',
        'pending' => 'در انتظار', 'sent' => 'ارسال شده', 'canceled' => 'لغو شده'];
?><!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>پنل مدیریت — <?= e($CFG['site_name']) ?></title>
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body style="background:var(--cream)">
<div class="grain" aria-hidden="true"></div>

<div class="adminbar">
  <div class="wrap">
    <b>پنل بانی‌پت</b>
    <a href="?tab=home"<?= active_if($tab === 'home') ?>>خلاصه</a>
    <a href="?tab=products"<?= active_if($tab === 'products') ?>>محصول‌ها</a>
    <a href="?tab=articles"<?= active_if($tab === 'articles') ?>>مقاله‌ها</a>
    <a href="?tab=guide"<?= active_if($tab === 'guide') ?>>راهنمای نگهداری</a>
    <a href="?tab=animals"<?= active_if($tab === 'animals') ?>>حیوون‌ها</a>
    <a href="?tab=orders"<?= active_if($tab === 'orders') ?>>سفارش‌ها</a>
    <a href="?tab=adoptions"<?= active_if($tab === 'adoptions') ?>>درخواست سرپرستی</a>
    <a href="?tab=leads"<?= active_if($tab === 'leads') ?>>درخواست مشاوره</a>
    <a href="?tab=users"<?= active_if($tab === 'users') ?>>کاربرها</a>
    <span class="sp"></span>
    <a href="<?= url('index.php') ?>">دیدن سایت</a>
    <a href="<?= url('logout.php') ?>">خروج</a>
  </div>
</div>

<main class="wrap" style="padding:26px 18px 60px">
<?php if ($msg): ?><div class="note ok"><?= e($msg) ?></div><?php endif; ?>

<?php if ($tab === 'home'):
  $sums = [
    'سفارش‌ها'        => one('SELECT COUNT(*) c FROM orders')['c'],
    'سفارش پرداخت‌شده' => one("SELECT COUNT(*) c FROM orders WHERE status='paid'")['c'],
    'کاربرها'         => one('SELECT COUNT(*) c FROM users')['c'],
    'درخواست مشاوره'   => one('SELECT COUNT(*) c FROM leads')['c'],
    'محصول‌ها'        => one('SELECT COUNT(*) c FROM products')['c'],
    'حیوون‌ها'        => one('SELECT COUNT(*) c FROM animals')['c'],
    'درخواست سرپرستی' => one('SELECT COUNT(*) c FROM adoptions')['c'],
    'مقاله‌ها'         => one('SELECT COUNT(*) c FROM articles')['c'],
  ];
  $rev = one("SELECT COALESCE(SUM(total),0) s FROM orders WHERE status IN ('paid','sent')");
?>
  <h1 class="h2" style="margin-bottom:16px">یه نگاه کلی</h1>
  <div class="adminstats">
    <?php foreach ($sums as $k => $v): ?>
      <div class="summary" style="position:static;text-align:center">
        <b style="font-size:26px;color:var(--pink)"><?= fa_num($v) ?></b>
        <div class="lead"><?= e($k) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="summary" style="position:static;margin-top:16px;text-align:center">
    <b style="font-size:26px;color:var(--blue)"><?= price_label($rev['s']) ?></b>
    <div class="lead">مجموع فروش تأییدشده</div>
  </div>
  <div class="note warn" style="margin-top:20px">
    یادت باشه: فایل <code>install.php</code> باید از روی هاست پاک شده باشه و رمز مدیر رو عوض کرده باشی.
  </div>

<?php elseif ($tab === 'products'): ?>
  <h1 class="h2" style="margin-bottom:14px"><?= $edit ? 'ویرایش محصول' : 'محصول‌ها' ?></h1>
  <div class="summary" style="position:static;margin-bottom:22px">
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="save_product" value="1">
      <input type="hidden" name="id" value="<?= $edit ? (int)$edit['id'] : 0 ?>">
      <div class="two">
        <div class="field"><label>عنوان</label>
          <input name="title" required maxlength="150" value="<?= e($edit['title'] ?? '') ?>"></div>
        <div class="field"><label>نشانی انگلیسی (slug)</label>
          <input name="slug" maxlength="80" value="<?= e($edit['slug'] ?? '') ?>" placeholder="mesle-in"></div>
      </div>
      <div class="field"><label>توضیح کوتاه</label>
        <input name="short" maxlength="250" value="<?= e($edit['short'] ?? '') ?>"></div>
      <div class="field"><label>توضیح کامل</label>
        <textarea name="body" maxlength="4000"><?= e($edit['body'] ?? '') ?></textarea></div>
      <div class="two">
        <div class="field"><label>قیمت (تومان)</label>
          <input name="price" inputmode="numeric" value="<?= (int)($edit['price'] ?? 0) ?>"></div>
        <div class="field"><label>قیمت قبلی (۰ یعنی ندارد)</label>
          <input name="old_price" inputmode="numeric" value="<?= (int)($edit['old_price'] ?? 0) ?>"></div>
      </div>
      <div class="two">
        <div class="field"><label>دسته</label>
          <select name="cat_id">
            <?php foreach ($cats as $c): ?>
              <option value="<?= (int)$c['id'] ?>" <?= (($edit['cat_id'] ?? 0) == $c['id']) ? 'selected' : '' ?>>
                <?= e($c['title']) ?></option>
            <?php endforeach; ?>
          </select></div>
        <div class="field"><label>مناسب کدوم حیوون</label>
          <select name="pet_id">
            <?php foreach ($petsAll as $pp): ?>
              <option value="<?= (int)$pp['id'] ?>" <?= (($edit['pet_id'] ?? 0) == $pp['id']) ? 'selected' : '' ?>>
                <?= e($pp['title']) ?></option>
            <?php endforeach; ?>
          </select></div>
      </div>
      <div class="two">
        <div class="field"><label>برچسب (پرفروش، تخفیف…)</label>
          <input name="badge" maxlength="30" value="<?= e($edit['badge'] ?? '') ?>"></div>
      </div>
      <div class="two">
        <div class="field"><label>موجودی</label>
          <input name="stock" inputmode="numeric" value="<?= (int)($edit['stock'] ?? 10) ?>"></div>
      </div>
      <div class="two">
        <div class="field"><label>رنگ کارت</label>
          <select name="tone">
            <option value="pink" <?= (($edit['tone'] ?? '') === 'pink') ? 'selected' : '' ?>>صورتی</option>
            <option value="blue" <?= (($edit['tone'] ?? '') === 'blue') ? 'selected' : '' ?>>آبی</option>
          </select></div>
        <div class="field"><label>نمایش در سایت</label>
          <label style="display:flex;gap:8px;align-items:center;padding-top:8px">
            <input type="checkbox" name="active" value="1" <?= (!$edit || $edit['active']) ? 'checked' : '' ?>
                   style="width:18px;height:18px;accent-color:var(--pink)"> فعال</label></div>
      </div>
      <button class="btn mini" type="submit"><?= $edit ? 'ذخیره تغییرات' : 'افزودن محصول' ?></button>
      <?php if ($edit): ?><a class="btn ghost mini" href="?tab=products">انصراف</a><?php endif; ?>
    </form>
  </div>

  <div class="tablewrap">
    <table>
      <thead><tr><th>عنوان</th><th>دسته</th><th>قیمت</th><th>موجودی</th><th>وضعیت</th><th></th></tr></thead>
      <tbody>
      <?php foreach (all('SELECT p.*, c.title ct FROM products p JOIN categories c ON c.id=p.cat_id ORDER BY p.id DESC') as $p): ?>
        <tr>
          <td><?= e($p['title']) ?></td>
          <td><?= e($p['ct']) ?></td>
          <td><?= money($p['price']) ?></td>
          <td><?= fa_num($p['stock']) ?></td>
          <td><span class="pill <?= $p['active'] ? 'paid' : 'failed' ?>"><?= $p['active'] ? 'فعال' : 'غیرفعال' ?></span></td>
          <td>
            <a class="btn ghost mini" href="?tab=products&edit=<?= (int)$p['id'] ?>">ویرایش</a>
            <form method="post" style="display:inline" onsubmit="return confirm('مطمئنی حذف بشه؟')">
              <?= csrf_field() ?>
              <button class="btn ghost mini" name="del_product" value="<?= (int)$p['id'] ?>">حذف</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<?php elseif ($tab === 'articles'): ?>
  <h1 class="h2" style="margin-bottom:14px"><?= $edit ? 'ویرایش مقاله' : 'مقاله‌ها' ?></h1>
  <div class="summary" style="position:static;margin-bottom:22px">
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="save_article" value="1">
      <input type="hidden" name="id" value="<?= $edit ? (int)$edit['id'] : 0 ?>">
      <div class="two">
        <div class="field"><label>عنوان</label>
          <input name="title" required maxlength="200" value="<?= e($edit['title'] ?? '') ?>"></div>
        <div class="field"><label>نشانی انگلیسی (slug)</label>
          <input name="slug" maxlength="90" value="<?= e($edit['slug'] ?? '') ?>"></div>
      </div>
      <div class="two">
        <div class="field"><label>موضوع</label>
          <input name="tag" maxlength="50" value="<?= e($edit['tag'] ?? '') ?>" placeholder="خرگوش"></div>
        <div class="field"><label>زمان خواندن (دقیقه)</label>
          <input name="read_min" inputmode="numeric" value="<?= (int)($edit['read_min'] ?? 4) ?>"></div>
      </div>
      <div class="field"><label>خلاصه</label>
        <input name="excerpt" maxlength="380" value="<?= e($edit['excerpt'] ?? '') ?>"></div>
      <div class="field"><label>متن مقاله (می‌تونی از تگ‌های h2 و p و ul استفاده کنی)</label>
        <textarea name="body" style="min-height:260px"><?= e($edit['body'] ?? '') ?></textarea></div>
      <div class="two">
        <div class="field"><label>رنگ کاور</label>
          <select name="tone">
            <option value="pink" <?= (($edit['tone'] ?? '') === 'pink') ? 'selected' : '' ?>>صورتی</option>
            <option value="blue" <?= (($edit['tone'] ?? '') === 'blue') ? 'selected' : '' ?>>آبی</option>
          </select></div>
        <div class="field"><label>نمایش</label>
          <label style="display:flex;gap:8px;align-items:center;padding-top:8px">
            <input type="checkbox" name="active" value="1" <?= (!$edit || $edit['active']) ? 'checked' : '' ?>
                   style="width:18px;height:18px;accent-color:var(--pink)"> منتشر شده</label></div>
      </div>
      <button class="btn mini" type="submit"><?= $edit ? 'ذخیره' : 'افزودن مقاله' ?></button>
      <?php if ($edit): ?><a class="btn ghost mini" href="?tab=articles">انصراف</a><?php endif; ?>
    </form>
  </div>

  <div class="tablewrap">
    <table>
      <thead><tr><th>عنوان</th><th>موضوع</th><th>بازدید</th><th>تاریخ</th><th></th></tr></thead>
      <tbody>
      <?php foreach (all('SELECT * FROM articles ORDER BY id DESC') as $a): ?>
        <tr>
          <td><?= e(excerpt($a['title'], 46)) ?></td>
          <td><?= e($a['tag']) ?></td>
          <td><?= fa_num($a['views']) ?></td>
          <td><?= e(fa_date($a['created_at'])) ?></td>
          <td>
            <a class="btn ghost mini" href="?tab=articles&edit=<?= (int)$a['id'] ?>">ویرایش</a>
            <form method="post" style="display:inline" onsubmit="return confirm('حذف بشه؟')">
              <?= csrf_field() ?>
              <button class="btn ghost mini" name="del_article" value="<?= (int)$a['id'] ?>">حذف</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<?php elseif ($tab === 'guide'): ?>
  <h1 class="h2" style="margin-bottom:6px">راهنمای نگهداری</h1>
  <p class="lead" style="margin-bottom:14px">
    برای هر بخش می‌تونی اسم فایل عکس و لینک ویدیو بذاری.
    عکس‌ها رو توی پوشه <code>assets/img/guide/</code> آپلود کن و همون اسم فایل رو اینجا بنویس
    (مثلاً <code>01-taghziye.jpg</code>). لینک ویدیو می‌تونه آپارات، یوتیوب یا فایل mp4 باشه.
  </p>

  <?php if ($edit): ?>
    <div class="summary" style="position:static;margin-bottom:22px">
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="save_gsec" value="1">
        <input type="hidden" name="id" value="<?= (int)$edit['id'] ?>">
        <div class="two">
          <div class="field"><label>عنوان بخش <?= fa_num($edit['num']) ?></label>
            <input name="title" required maxlength="200" value="<?= e($edit['title']) ?>"></div>
          <div class="field"><label>نشانی انگلیسی صفحه</label>
            <input name="slug" maxlength="90" value="<?= e($edit['slug']) ?>"></div>
        </div>
        <div class="field"><label>متن</label>
          <textarea name="body" maxlength="2000" style="min-height:150px"><?= e($edit['body']) ?></textarea></div>
        <div class="two">
          <div class="field"><label>اسم فایل عکس</label>
            <input name="img" maxlength="200" value="<?= e($edit['img']) ?>" placeholder="01-taghziye.jpg"></div>
          <div class="field"><label>لینک ویدیو (آپارات، یوتیوب یا mp4)</label>
            <input name="video" maxlength="300" value="<?= e($edit['video']) ?>"
                   placeholder="https://www.aparat.com/v/xxxxx"></div>
        </div>
        <div class="field"><label>✅ انجام بده</label>
          <input name="do_tip" maxlength="300" value="<?= e($edit['do_tip']) ?>"></div>
        <div class="field"><label>❌ انجام نده</label>
          <input name="dont_tip" maxlength="300" value="<?= e($edit['dont_tip']) ?>"></div>
        <div class="field"><label>🚨 خطر</label>
          <input name="danger_tip" maxlength="300" value="<?= e($edit['danger_tip']) ?>"></div>
        <label style="display:flex;gap:8px;align-items:center;margin-bottom:14px">
          <input type="checkbox" name="active" value="1" <?= $edit['active'] ? 'checked' : '' ?>
                 style="width:18px;height:18px;accent-color:var(--pink)"> نمایش این بخش</label>
        <button class="btn mini" type="submit">ذخیره</button>
        <a class="btn ghost mini" href="?tab=guide">انصراف</a>
      </form>
    </div>
  <?php endif; ?>

  <div class="tablewrap">
    <table>
      <thead><tr><th>#</th><th>عنوان</th><th>عکس</th><th>ویدیو</th><th>وضعیت</th><th></th></tr></thead>
      <tbody>
      <?php foreach (all('SELECT * FROM guide_sections ORDER BY num') as $gs): ?>
        <tr>
          <td><?= fa_num($gs['num']) ?></td>
          <td><?= e($gs['title']) ?></td>
          <td><?= $gs['img'] ? '✔' : '—' ?></td>
          <td><?= $gs['video'] ? '✔' : '—' ?></td>
          <td><span class="pill <?= $gs['active'] ? 'paid' : 'failed' ?>">
            <?= $gs['active'] ? 'فعال' : 'مخفی' ?></span></td>
          <td><a class="btn ghost mini" href="?tab=guide&edit=<?= (int)$gs['id'] ?>">ویرایش</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<?php elseif ($tab === 'animals'): ?>
  <h1 class="h2" style="margin-bottom:14px"><?= $edit ? 'ویرایش پرونده' : 'حیوون‌های آماده سرپرستی' ?></h1>
  <div class="summary" style="position:static;margin-bottom:22px">
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="save_animal" value="1">
      <input type="hidden" name="id" value="<?= $edit ? (int)$edit['id'] : 0 ?>">
      <div class="two">
        <div class="field"><label>اسم حیوون</label>
          <input name="name" required maxlength="80" value="<?= e($edit['name'] ?? '') ?>"></div>
        <div class="field"><label>نشانی انگلیسی (slug)</label>
          <input name="slug" maxlength="80" value="<?= e($edit['slug'] ?? '') ?>"></div>
      </div>
      <div class="two">
        <div class="field"><label>خانواده</label>
          <select name="pet_id">
            <?php foreach ($petsAll as $pp): ?>
              <option value="<?= (int)$pp['id'] ?>" <?= (($edit['pet_id'] ?? 0) == $pp['id']) ? 'selected' : '' ?>>
                <?= e($pp['title']) ?></option>
            <?php endforeach; ?>
          </select></div>
        <div class="field"><label>نژاد</label>
          <input name="breed" maxlength="80" value="<?= e($edit['breed'] ?? '') ?>"></div>
      </div>
      <div class="two">
        <div class="field"><label>سن</label>
          <input name="age" maxlength="60" value="<?= e($edit['age'] ?? '') ?>" placeholder="۳ ماه"></div>
        <div class="field"><label>جنسیت</label>
          <select name="sex">
            <option <?= (($edit['sex'] ?? '') === 'نر') ? 'selected' : '' ?>>نر</option>
            <option <?= (($edit['sex'] ?? '') === 'ماده') ? 'selected' : '' ?>>ماده</option>
          </select></div>
      </div>
      <div class="two">
        <div class="field"><label>ارزش سرپرستی (تومان)</label>
          <input name="worth" inputmode="numeric" value="<?= (int)($edit['worth'] ?? 0) ?>"></div>
        <div class="field"><label>وضعیت</label>
          <select name="status">
            <option value="ready" <?= (($edit['status'] ?? '') === 'ready') ? 'selected' : '' ?>>آماده سرپرستی</option>
            <option value="reserved" <?= (($edit['status'] ?? '') === 'reserved') ? 'selected' : '' ?>>رزرو شده</option>
            <option value="adopted" <?= (($edit['status'] ?? '') === 'adopted') ? 'selected' : '' ?>>سرپرست پیدا کرده</option>
          </select></div>
      </div>
      <div class="field"><label>معرفی و شخصیت</label>
        <textarea name="story" maxlength="1500"><?= e($edit['story'] ?? '') ?></textarea></div>
      <div class="field"><label>وضعیت سلامت و نکات مراقبتی</label>
        <textarea name="care" maxlength="800"><?= e($edit['care'] ?? '') ?></textarea></div>
      <div class="two">
        <div class="field"><label>رنگ کارت</label>
          <select name="tone">
            <option value="pink" <?= (($edit['tone'] ?? '') === 'pink') ? 'selected' : '' ?>>صورتی</option>
            <option value="blue" <?= (($edit['tone'] ?? '') === 'blue') ? 'selected' : '' ?>>آبی</option>
          </select></div>
        <div class="field"><label>نمایش</label>
          <label style="display:flex;gap:8px;align-items:center;padding-top:8px">
            <input type="checkbox" name="active" value="1" <?= (!$edit || $edit['active']) ? 'checked' : '' ?>
                   style="width:18px;height:18px;accent-color:var(--pink)"> فعال</label></div>
      </div>
      <button class="btn mini" type="submit"><?= $edit ? 'ذخیره' : 'افزودن حیوون' ?></button>
      <?php if ($edit): ?><a class="btn ghost mini" href="?tab=animals">انصراف</a><?php endif; ?>
    </form>
  </div>
  <div class="tablewrap">
    <table>
      <thead><tr><th>اسم</th><th>خانواده</th><th>نژاد</th><th>ارزش سرپرستی</th><th>وضعیت</th><th></th></tr></thead>
      <tbody>
      <?php foreach (all('SELECT a.*, p.title pt FROM animals a JOIN pets p ON p.id=a.pet_id ORDER BY a.id DESC') as $an): ?>
        <tr>
          <td><?= e($an['name']) ?></td>
          <td><?= e($an['pt']) ?></td>
          <td><?= e($an['breed']) ?></td>
          <td><?= money($an['worth']) ?></td>
          <td><span class="pill <?= $an['status'] === 'ready' ? 'paid' : 'pending' ?>">
            <?= e(status_label($an['status'])) ?></span></td>
          <td>
            <a class="btn ghost mini" href="?tab=animals&edit=<?= (int)$an['id'] ?>">ویرایش</a>
            <form method="post" style="display:inline" onsubmit="return confirm('حذف بشه؟')">
              <?= csrf_field() ?>
              <button class="btn ghost mini" name="del_animal" value="<?= (int)$an['id'] ?>">حذف</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<?php elseif ($tab === 'adoptions'):
  $albl = ['new' => 'جدید', 'called' => 'تماس گرفته شد', 'approved' => 'تأیید شده',
           'rejected' => 'رد شده', 'done' => 'تحویل شد']; ?>
  <h1 class="h2" style="margin-bottom:6px">درخواست‌های سرپرستی</h1>
  <p class="lead" style="margin-bottom:14px">هر درخواست یعنی یکی آماده‌ست حرف بزنه. سریع جواب بده.</p>
  <div class="tablewrap">
    <table>
      <thead><tr><th>کد</th><th>حیوون</th><th>اسم</th><th>موبایل</th><th>شهر</th><th>خونه</th><th>تاریخ</th><th>وضعیت</th></tr></thead>
      <tbody>
      <?php foreach (all('SELECT ad.*, an.name aname FROM adoptions ad
                          LEFT JOIN animals an ON an.id = ad.animal_id
                          ORDER BY ad.id DESC LIMIT 200') as $ad): ?>
        <tr>
          <td><?= e(fa_num($ad['code'])) ?></td>
          <td><?= e($ad['aname']) ?></td>
          <td><?= e($ad['name']) ?></td>
          <td><?= e(fa_num($ad['phone'])) ?></td>
          <td><?= e($ad['city']) ?></td>
          <td><?= e($ad['home']) ?></td>
          <td><?= e(fa_date($ad['created_at'])) ?></td>
          <td>
            <form method="post" style="display:flex;gap:6px">
              <?= csrf_field() ?>
              <input type="hidden" name="adoption_status" value="<?= (int)$ad['id'] ?>">
              <select name="status" style="padding:5px 8px;border-radius:10px;border:1px solid var(--line)">
                <?php foreach ($albl as $k => $v): ?>
                  <option value="<?= e($k) ?>" <?= $ad['status'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
              <button class="btn ghost mini">ثبت</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<?php elseif ($tab === 'orders'): ?>
  <h1 class="h2" style="margin-bottom:14px">سفارش‌ها</h1>
  <div class="tablewrap">
    <table>
      <thead><tr><th>کد</th><th>مشتری</th><th>موبایل</th><th>مبلغ</th><th>روش</th><th>تاریخ</th><th>وضعیت</th></tr></thead>
      <tbody>
      <?php foreach (all('SELECT * FROM orders ORDER BY id DESC LIMIT 200') as $o): ?>
        <tr>
          <td><?= e(fa_num($o['code'])) ?></td>
          <td><?= e($o['name']) ?></td>
          <td><?= e(fa_num($o['phone'])) ?></td>
          <td><?= money($o['total']) ?></td>
          <td><?= e($o['gateway']) ?></td>
          <td><?= e(fa_date($o['created_at'])) ?></td>
          <td>
            <form method="post" style="display:flex;gap:6px">
              <?= csrf_field() ?>
              <input type="hidden" name="order_status" value="<?= (int)$o['id'] ?>">
              <select name="status" style="padding:5px 8px;border-radius:10px;border:1px solid var(--line)">
                <?php foreach ($lbl as $k => $v): ?>
                  <option value="<?= e($k) ?>" <?= $o['status'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
              <button class="btn ghost mini">ثبت</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<?php elseif ($tab === 'leads'): ?>
  <h1 class="h2" style="margin-bottom:6px">درخواست‌های مشاوره</h1>
  <p class="lead" style="margin-bottom:14px">اینا باارزش‌ترین دارایی سایتن. هر کدوم رو تماس گرفتی، یه جا علامت بزن.</p>
  <div class="tablewrap">
    <table>
      <thead><tr><th>اسم</th><th>موبایل</th><th>حیوون</th><th>از کجا</th><th>تاریخ</th></tr></thead>
      <tbody>
      <?php foreach (all('SELECT * FROM leads ORDER BY id DESC LIMIT 300') as $l): ?>
        <tr>
          <td><?= e($l['name']) ?></td>
          <td><?= e(fa_num($l['phone'])) ?></td>
          <td><?= e($l['pet_type']) ?></td>
          <td><?= e($l['source']) ?></td>
          <td><?= e(fa_date($l['created_at'])) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

<?php else: ?>
  <h1 class="h2" style="margin-bottom:14px">کاربرها</h1>
  <div class="tablewrap">
    <table>
      <thead><tr><th>اسم</th><th>موبایل</th><th>ایمیل</th><th>حیوون</th><th>نقش</th><th>عضویت</th></tr></thead>
      <tbody>
      <?php foreach (all('SELECT * FROM users ORDER BY id DESC LIMIT 300') as $u): ?>
        <tr>
          <td><?= e($u['name']) ?></td>
          <td><?= e(fa_num($u['phone'])) ?></td>
          <td><?= e($u['email']) ?></td>
          <td><?= e($u['pet_type']) ?></td>
          <td><?= $u['role'] === 'admin' ? 'مدیر' : 'مشتری' ?></td>
          <td><?= e(fa_date($u['created_at'])) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
</main>

<script src="<?= url('assets/js/main.js') ?>" defer></script>
</body>
</html>
