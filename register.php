<?php
require_once __DIR__ . '/inc/functions.php';
if (current_user()) { redirect('account.php'); }

$err = '';
$cats = all('SELECT title FROM categories ORDER BY sort_order');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $name  = clean($_POST['name'], 60);
  $phone = valid_phone(isset($_POST['phone']) ? $_POST['phone'] : '');
  $email = clean(isset($_POST['email']) ? $_POST['email'] : '', 120);
  $pet   = clean(isset($_POST['pet_type']) ? $_POST['pet_type'] : '', 40);
  $pass  = isset($_POST['pass']) ? (string)$_POST['pass'] : '';
  $pass2 = isset($_POST['pass2']) ? (string)$_POST['pass2'] : '';

  if ($name === '')                             { $err = 'اسمت رو بنویس'; }
  elseif (!$phone)                              { $err = 'شماره موبایل باید با ۰۹ شروع بشه و ۱۱ رقم باشه'; }
  elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) { $err = 'ایمیل درست نیست'; }
  elseif (mb_strlen($pass, 'UTF-8') < 8)        { $err = 'رمز حداقل ۸ کاراکتر باشه'; }
  elseif ($pass !== $pass2)                     { $err = 'دو تا رمز مثل هم نیستن'; }
  elseif (one('SELECT id FROM users WHERE phone = ?', [$phone])) {
    $err = 'با این شماره قبلاً حساب ساخته شده. وارد شو.';
  } else {
    q('INSERT INTO users (name, phone, email, pass_hash, pet_type, role, created_at)
       VALUES (?,?,?,?,?,?,?)',
      [$name, $phone, $email, password_hash($pass, PASSWORD_DEFAULT), $pet, 'user',
       date('Y-m-d H:i:s')]);
    session_regenerate_id(true);
    $_SESSION['uid'] = last_id();
    flash('خوش اومدی ' . $name . '، حسابت ساخته شد', 'ok');
    redirect('account.php');
  }
}

$PAGE_TITLE = 'ساخت حساب — ' . $CFG['site_name'];
require __DIR__ . '/inc/header.php';
?>

<div class="authwrap">
  <div class="dust" aria-hidden="true"></div>
  <div class="authcard">
    <span class="kicker">عضویت</span>
    <h1 class="h2">بیا عضو بانی‌پت شو</h1>
    <p class="lead">حساب که داشته باشی، سفارش‌هات و یادآوری خرید دوباره‌ت رو برات نگه می‌داریم.</p>

    <?php if ($err): ?><div class="note err"><?= e($err) ?></div><?php endif; ?>

    <form method="post" data-once>
      <?= csrf_field() ?>
      <div class="two">
        <div class="field">
          <label for="n">اسمت</label>
          <input id="n" name="name" required maxlength="60" autocomplete="name"
                 value="<?= e(isset($_POST['name']) ? $_POST['name'] : '') ?>">
        </div>
        <div class="field">
          <label for="p">شماره موبایل</label>
          <input id="p" name="phone" required inputmode="tel" maxlength="15" autocomplete="tel"
                 placeholder="۰۹۱۲۳۴۵۶۷۸۹"
                 value="<?= e(isset($_POST['phone']) ? $_POST['phone'] : '') ?>">
          <span class="hint">همین شماره، نام کاربری توئه</span>
        </div>
      </div>
      <div class="field">
        <label for="em">ایمیل (اختیاری)</label>
        <input id="em" name="email" type="email" maxlength="120" autocomplete="email"
               value="<?= e(isset($_POST['email']) ? $_POST['email'] : '') ?>">
      </div>
      <div class="field">
        <label for="pt">حیوون خونگیت چیه</label>
        <select id="pt" name="pet_type">
          <?php foreach ($cats as $c): ?>
            <option value="<?= e($c['title']) ?>"><?= e($c['title']) ?></option>
          <?php endforeach; ?>
          <option value="هنوز ندارم">هنوز ندارم</option>
        </select>
        <span class="hint">این رو می‌پرسیم که پیشنهادهای بی‌ربط بهت ندیم</span>
      </div>
      <div class="two">
        <div class="field">
          <label for="pw">رمز عبور</label>
          <input id="pw" name="pass" type="password" required minlength="8" autocomplete="new-password">
          <span class="hint">حداقل ۸ کاراکتر</span>
        </div>
        <div class="field">
          <label for="pw2">تکرار رمز</label>
          <input id="pw2" name="pass2" type="password" required minlength="8" autocomplete="new-password">
        </div>
      </div>
      <button class="btn wide" type="submit">ساخت حساب</button>
    </form>

    <div class="authfoot">
      حساب داری؟ <a href="<?= url('login.php') ?>">وارد شو</a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>
