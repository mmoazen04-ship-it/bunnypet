<?php
require_once __DIR__ . '/inc/functions.php';
require_login();
$U = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  if (isset($_POST['profile'])) {
    $name = clean($_POST['name'], 60);
    $mail = clean(isset($_POST['email']) ? $_POST['email'] : '', 120);
    $pet  = clean(isset($_POST['pet_type']) ? $_POST['pet_type'] : '', 40);
    if ($name === '') { flash('اسم نمی‌تونه خالی باشه', 'err'); }
    else {
      q('UPDATE users SET name = ?, email = ?, pet_type = ? WHERE id = ?',
        [$name, $mail, $pet, $U['id']]);
      flash('اطلاعاتت ذخیره شد', 'ok');
    }
    redirect('account.php');
  }
  if (isset($_POST['passchange'])) {
    $old = (string)$_POST['old'];
    $new = (string)$_POST['new'];
    $row = one('SELECT pass_hash FROM users WHERE id = ?', [$U['id']]);
    if (!password_verify($old, $row['pass_hash'])) { flash('رمز فعلی درست نیست', 'err'); }
    elseif (mb_strlen($new, 'UTF-8') < 8) { flash('رمز جدید حداقل ۸ کاراکتر باشه', 'err'); }
    else {
      q('UPDATE users SET pass_hash = ? WHERE id = ?',
        [password_hash($new, PASSWORD_DEFAULT), $U['id']]);
      flash('رمزت عوض شد', 'ok');
    }
    redirect('account.php');
  }
}

$orders = all('SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC', [$U['id']]);
$cats = all('SELECT title FROM categories ORDER BY sort_order');
$PAGE_TITLE = 'حساب من — ' . $CFG['site_name'];
require __DIR__ . '/inc/header.php';
?>

<section class="pagehead">
  <div class="dust" aria-hidden="true"></div>
  <div class="wrap">
    <div class="crumb"><a href="<?= url('index.php') ?>">خانه</a> ← حساب من</div>
    <h1 class="h2">سلام <?= e($U['name']) ?></h1>
    <p class="lead">عضو بانی‌پت از <?= e(fa_date($U['created_at'])) ?></p>
    <?php if (is_admin()): ?>
      <a class="btn mini" style="margin-top:10px" href="<?= url('admin/index.php') ?>">پنل مدیریت</a>
    <?php endif; ?>
  </div>
</section>

<div class="wrap">
  <div class="layout">
    <aside class="side">
      <h3>حساب من</h3>
      <ul>
        <li><a href="#orders">سفارش‌ها</a></li>
        <li><a href="#profile">اطلاعات من</a></li>
        <li><a href="#pass">تغییر رمز</a></li>
        <li><a href="<?= url('logout.php') ?>">خروج</a></li>
      </ul>
    </aside>

    <div>
      <section id="orders" style="margin-bottom:28px">
        <h2 class="h3" style="margin-bottom:14px">سفارش‌های من</h2>
        <?php if (!$orders): ?>
          <div class="empty" style="padding:36px 18px">
            <div class="big">📦</div>
            <p>هنوز سفارشی ثبت نکردی.</p>
            <a class="btn" href="<?= url('shop.php') ?>">رفتن به فروشگاه</a>
          </div>
        <?php else: ?>
          <div class="tablewrap">
            <table>
              <thead><tr><th>کد</th><th>تاریخ</th><th>مبلغ</th><th>روش</th><th>وضعیت</th></tr></thead>
              <tbody>
              <?php foreach ($orders as $o):
                $cls = $o['status'] === 'paid' ? 'paid' : ($o['status'] === 'failed' ? 'failed' : 'pending');
                $lbl = ['paid' => 'پرداخت شده', 'failed' => 'ناموفق', 'awaiting' => 'در انتظار پرداخت',
                        'pending' => 'در انتظار', 'sent' => 'ارسال شده'];
              ?>
                <tr>
                  <td><?= e(fa_num($o['code'])) ?></td>
                  <td><?= e(fa_date($o['created_at'])) ?></td>
                  <td><?= money($o['total']) ?></td>
                  <td><?= e($o['gateway']) ?></td>
                  <td><span class="pill <?= $cls ?>">
                    <?= e(isset($lbl[$o['status']]) ? $lbl[$o['status']] : $o['status']) ?></span></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </section>

      <section id="profile" class="summary" style="position:static;margin-bottom:22px">
        <h2 class="h3" style="margin-bottom:14px">اطلاعات من</h2>
        <form method="post" data-once>
          <?= csrf_field() ?>
          <input type="hidden" name="profile" value="1">
          <div class="two">
            <div class="field">
              <label for="n">اسم</label>
              <input id="n" name="name" required maxlength="60" value="<?= e($U['name']) ?>">
            </div>
            <div class="field">
              <label for="em">ایمیل</label>
              <input id="em" name="email" type="email" maxlength="120" value="<?= e($U['email']) ?>">
            </div>
          </div>
          <div class="field">
            <label for="pt">حیوون خونگی</label>
            <select id="pt" name="pet_type">
              <?php foreach ($cats as $c): ?>
                <option value="<?= e($c['title']) ?>" <?= $U['pet_type'] === $c['title'] ? 'selected' : '' ?>>
                  <?= e($c['title']) ?></option>
              <?php endforeach; ?>
              <option value="هنوز ندارم" <?= $U['pet_type'] === 'هنوز ندارم' ? 'selected' : '' ?>>هنوز ندارم</option>
            </select>
          </div>
          <div class="field">
            <label>شماره موبایل</label>
            <input value="<?= e(fa_num($U['phone'])) ?>" disabled>
            <span class="hint">برای تغییر شماره با پشتیبانی تماس بگیر</span>
          </div>
          <button class="btn mini" type="submit">ذخیره</button>
        </form>
      </section>

      <section id="pass" class="summary" style="position:static">
        <h2 class="h3" style="margin-bottom:14px">تغییر رمز</h2>
        <form method="post" data-once>
          <?= csrf_field() ?>
          <input type="hidden" name="passchange" value="1">
          <div class="two">
            <div class="field">
              <label for="o">رمز فعلی</label>
              <input id="o" name="old" type="password" required autocomplete="current-password">
            </div>
            <div class="field">
              <label for="nw">رمز جدید</label>
              <input id="nw" name="new" type="password" required minlength="8" autocomplete="new-password">
            </div>
          </div>
          <button class="btn mini" type="submit">تغییر رمز</button>
        </form>
      </section>
    </div>
  </div>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>
