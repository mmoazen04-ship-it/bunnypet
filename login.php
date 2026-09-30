<?php
require_once __DIR__ . '/inc/functions.php';
if (current_user()) { redirect('account.php'); }

$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $phone = valid_phone(isset($_POST['phone']) ? $_POST['phone'] : '');
  $pass  = isset($_POST['pass']) ? (string)$_POST['pass'] : '';
  $key   = 'login:' . ($phone ? $phone : 'x');

  if (!login_attempts_ok($key)) {
    $err = 'چند بار اشتباه وارد کردی. ' . fa_num($CFG['login_lock_minutes'])
         . ' دقیقه دیگه دوباره امتحان کن.';
  } elseif (!$phone || $pass === '') {
    $err = 'شماره و رمز رو کامل وارد کن';
  } else {
    $u = one('SELECT * FROM users WHERE phone = ?', [$phone]);
    if ($u && password_verify($pass, $u['pass_hash'])) {
      login_attempt_clear($key);
      session_regenerate_id(true);
      $_SESSION['uid'] = $u['id'];
      if (password_needs_rehash($u['pass_hash'], PASSWORD_DEFAULT)) {
        q('UPDATE users SET pass_hash = ? WHERE id = ?',
          [password_hash($pass, PASSWORD_DEFAULT), $u['id']]);
      }
      $go = isset($_SESSION['after_login']) ? $_SESSION['after_login'] : null;
      unset($_SESSION['after_login']);
      flash('سلام ' . $u['name'] . '، خوش برگشتی', 'ok');
      redirect($go ? $go : ($u['role'] === 'admin' ? 'admin/index.php' : 'account.php'));
    }
    login_attempt_log($key);
    $err = 'شماره یا رمز درست نیست';
  }
}

$PAGE_TITLE = 'ورود — ' . $CFG['site_name'];
require __DIR__ . '/inc/header.php';
?>

<div class="authwrap">
  <div class="dust" aria-hidden="true"></div>
  <div class="authcard">
    <span class="kicker">ورود</span>
    <h1 class="h2">خوش برگشتی</h1>
    <p class="lead">با شماره موبایل و رمزت وارد شو.</p>

    <?php if ($err): ?><div class="note err"><?= e($err) ?></div><?php endif; ?>

    <form method="post" data-once>
      <?= csrf_field() ?>
      <div class="field">
        <label for="p">شماره موبایل</label>
        <input id="p" name="phone" required inputmode="tel" maxlength="15" autocomplete="tel"
               value="<?= e(isset($_POST['phone']) ? $_POST['phone'] : '') ?>">
      </div>
      <div class="field">
        <label for="pw">رمز عبور</label>
        <input id="pw" name="pass" type="password" required autocomplete="current-password">
      </div>
      <button class="btn wide" type="submit">ورود</button>
    </form>

    <div class="authfoot">
      هنوز حساب نداری؟ <a href="<?= url('register.php') ?>">یه حساب بساز</a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>
