<?php
/* ==========================================================================
   توابع مشترک، نشست امن، محافظت CSRF، سبد خرید
   ========================================================================== */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

/* ---------- ۰) اگه افزونه mbstring روی هاست نصب نبود ---------- */
if (!function_exists('mb_strlen')) {
  function mb_strlen($s, $enc = 'UTF-8') {
    return strlen(preg_replace('/[\x80-\xBF]/', '', (string)$s));
  }
}
if (!function_exists('mb_substr')) {
  function mb_substr($s, $start, $len = null, $enc = 'UTF-8') {
    preg_match_all('/./us', (string)$s, $m);
    $sl = array_slice($m[0], $start, $len);
    return implode('', $sl);
  }
}

/* ---------- ۱) هدرهای امنیتی ---------- */
if (!headers_sent()) {
  header('X-Content-Type-Options: nosniff');
  header('X-Frame-Options: SAMEORIGIN');
  header('Referrer-Policy: strict-origin-when-cross-origin');
  header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()');
  header("Content-Security-Policy: default-src 'self'; img-src 'self' data: https:; "
       . "style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; "
       . "font-src 'self'; media-src 'self' https:; "
       . "frame-src https://www.aparat.com https://aparat.com https://www.youtube.com "
       . "https://www.youtube-nocookie.com; "
       . "form-action 'self' https:; frame-ancestors 'self'; base-uri 'self'");
  header_remove('X-Powered-By');
}

/* ---------- ۲) اجبار به HTTPS ---------- */
if (!empty($CFG['force_https']) && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off')
    && PHP_SAPI !== 'cli') {
  header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
  exit;
}

/* ---------- ۳) نشست امن ---------- */
if (session_status() === PHP_SESSION_NONE) {
  $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
  session_name('bpsess');
  if (PHP_VERSION_ID >= 70300) {
    session_set_cookie_params([
      'lifetime' => 0, 'path' => '/', 'domain' => '',
      'secure' => $secure, 'httponly' => true, 'samesite' => 'Lax',
    ]);
  } else {
    session_set_cookie_params(0, '/; samesite=Lax', '', $secure, true);
  }
  session_start();
  if (empty($_SESSION['_born'])) {
    $_SESSION['_born'] = time();
    session_regenerate_id(true);
  }
}

/* ---------- ۴) خروجی امن ---------- */
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

/* ---------- آدرس‌های تمیز ----------
   url('article.php?slug=x')  →  /maghale/x
   اگه روی هاست mod_rewrite نبود، توی config گزینه pretty_urls رو false کن
   تا همون آدرس‌های ساده استفاده بشه. هیچ لینکی نمی‌شکنه. */
function pretty_map($path) {
  $frag = '';
  if (strpos($path, '#') !== false) {
    list($path, $frag) = explode('#', $path, 2);
    $frag = '#' . $frag;
  }
  $qs = '';
  if (strpos($path, '?') !== false) { list($path, $qs) = explode('?', $path, 2); }
  parse_str($qs, $a);
  $g = function ($k) use ($a) { return isset($a[$k]) ? rawurlencode($a[$k]) : ''; };

  switch ($path) {
    case '': case 'index.php':   return '' . $frag;
    case 'article.php': return ($g('slug') ? 'maghale/' . $g('slug') : 'majalle') . $frag;
    case 'product.php': return ($g('slug') ? 'mahsool/' . $g('slug') : 'forooshgah') . $frag;
    case 'animal.php':  return ($g('slug') ? 'parvande/' . $g('slug') : 'sarparasti') . $frag;
    case 'pet.php':     return ($g('slug') ? 'heyvan/' . $g('slug') : '') . $frag;
    case 'adopt.php':   return ('sarparasti' . ($g('pet') ? '/' . $g('pet') : '')) . $frag;
    case 'blog.php':    return ('majalle' . ($g('tag') ? '/mozoo/' . $g('tag') : '')) . $frag;
    case 'shop.php':
      if ($g('q') !== '') return 'shop.php?q=' . $g('q') . $frag;   /* جستجو ساده می‌مونه */
      $u = 'forooshgah';
      if ($g('cat')) { $u .= '/daste/' . $g('cat'); }
      if ($g('pet')) { $u .= '/heyvan/' . $g('pet'); }
      return $u . $frag;
    case 'rahnama.php':  return 'rahnama/' . ($g('pet') ? $g('pet') : 'khargoosh')
                              . ($g('b') ? '/' . $g('b') : '') . $frag;
    case 'about.php':    return 'darbare' . $frag;
    case 'faq.php':      return 'porsesh' . $frag;
    case 'contact.php':  return 'tamas' . $frag;
    case 'cart.php':     return 'sabad' . $frag;
    case 'checkout.php': return 'tasviye' . $frag;
    case 'login.php':    return 'vorood' . $frag;
    case 'register.php': return 'sabtenam' . $frag;
    case 'account.php':  return 'hesab' . $frag;
    case 'logout.php':   return 'khorooj' . $frag;
  }
  return null;
}

function url($path = '') {
  global $CFG;
  $path = ltrim($path, '/');
  if (!empty($CFG['pretty_urls']) && strpos($path, 'assets/') !== 0) {
    $m = pretty_map($path);
    if ($m !== null) { return BASE_URL . '/' . $m; }
  }
  return BASE_URL . '/' . $path;
}

/* آدرس کامل و یکتای همین صفحه، برای تگ canonical */
function canonical($path) { return url($path); }

function redirect($path) { header('Location: ' . (strpos($path, 'http') === 0 ? $path : url($path))); exit; }

/* ---------- ۵) عدد فارسی و قیمت ---------- */
function fa_num($s) {
  $en = ['0','1','2','3','4','5','6','7','8','9'];
  $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
  return str_replace($en, $fa, (string)$s);
}
function en_num($s) {
  $en = ['0','1','2','3','4','5','6','7','8','9'];
  $fa = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
  $ar = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
  return str_replace(array_merge($fa, $ar), array_merge($en, $en), (string)$s);
}
function money($n) { return fa_num(number_format((int)$n)); }
function price_label($n) {
  global $CFG;
  return money($n) . ' ' . $CFG['currency'];
}

/* ---------- ۶) محافظت CSRF ---------- */
function csrf_token() {
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf'];
}
function csrf_field() {
  return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}
function csrf_check() {
  $t = isset($_POST['_token']) ? $_POST['_token'] : '';
  if (empty($_SESSION['csrf']) || !is_string($t) || !hash_equals($_SESSION['csrf'], $t)) {
    http_response_code(419);
    exit('<div style="font-family:Tahoma;direction:rtl;padding:40px">
      نشست شما منقضی شده. صفحه رو تازه کن و دوباره امتحان کن.</div>');
  }
}

/* ---------- ۷) پیام‌های موقت ---------- */
function flash($msg = null, $type = 'ok') {
  if ($msg === null) {
    if (empty($_SESSION['flash'])) return null;
    $f = $_SESSION['flash']; unset($_SESSION['flash']); return $f;
  }
  $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
  return null;
}

/* ---------- ۸) کاربر ---------- */
function current_user() {
  static $u = false;
  if ($u !== false) return $u;
  if (empty($_SESSION['uid'])) return $u = null;
  $u = one('SELECT id, name, phone, email, role, pet_type, created_at FROM users WHERE id = ?',
           [$_SESSION['uid']]);
  return $u;
}
function is_admin() {
  $u = current_user();
  return $u && $u['role'] === 'admin';
}
function require_login() {
  if (!current_user()) {
    $_SESSION['after_login'] = $_SERVER['REQUEST_URI'];
    flash('برای ادامه اول وارد حسابت شو', 'warn');
    redirect('login.php');
  }
}
function require_admin() {
  if (!is_admin()) { redirect('login.php'); }
}

/* ---------- ۹) اعتبارسنجی ---------- */
function valid_phone($p) {
  $p = en_num(trim($p));
  $p = preg_replace('/\D/', '', $p);
  if (strpos($p, '98') === 0) $p = '0' . substr($p, 2);
  if (preg_match('/^09\d{9}$/', $p)) return $p;
  return false;
}
function clean($s, $max = 200) {
  $s = trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', (string)$s));
  return mb_substr($s, 0, $max, 'UTF-8');
}

/* ---------- ۱۰) محدودیت تلاش ورود ---------- */
function login_attempts_ok($key) {
  global $CFG;
  $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0';
  $since = date('Y-m-d H:i:s', time() - $CFG['login_lock_minutes'] * 60);
  $row = one('SELECT COUNT(*) AS c FROM login_attempts WHERE ip = ? AND k = ? AND at > ?',
             [$ip, $key, $since]);
  return (int)$row['c'] < (int)$CFG['login_max_tries'];
}
function login_attempt_log($key) {
  $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0';
  q('INSERT INTO login_attempts (ip, k, at) VALUES (?, ?, ?)',
    [$ip, $key, date('Y-m-d H:i:s')]);
}
function login_attempt_clear($key) {
  $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0';
  q('DELETE FROM login_attempts WHERE ip = ? AND k = ?', [$ip, $key]);
}

/* ---------- ۱۱) سبد خرید (توی نشست نگه داشته می‌شه) ---------- */
function cart() { return isset($_SESSION['cart']) ? $_SESSION['cart'] : []; }
function cart_add($pid, $qty = 1) {
  $pid = (int)$pid; $qty = max(1, min(20, (int)$qty));
  $p = one('SELECT id FROM products WHERE id = ? AND active = 1', [$pid]);
  if (!$p) return false;
  $c = cart();
  $c[$pid] = isset($c[$pid]) ? min(20, $c[$pid] + $qty) : $qty;
  $_SESSION['cart'] = $c;
  return true;
}
function cart_set($pid, $qty) {
  $c = cart(); $pid = (int)$pid; $qty = (int)$qty;
  if ($qty <= 0) unset($c[$pid]); else $c[$pid] = min(20, $qty);
  $_SESSION['cart'] = $c;
}
function cart_count() { return array_sum(cart()); }
function cart_items() {
  $c = cart();
  if (!$c) return [];
  $ids = array_map('intval', array_keys($c));
  $in  = implode(',', array_fill(0, count($ids), '?'));
  $rows = all("SELECT * FROM products WHERE id IN ($in)", $ids);
  $out = [];
  foreach ($rows as $r) {
    $r['qty'] = (int)$c[$r['id']];
    $r['sum'] = $r['qty'] * (int)$r['price'];
    $out[] = $r;
  }
  return $out;
}
function cart_total() {
  $t = 0;
  foreach (cart_items() as $i) $t += $i['sum'];
  return $t;
}
function shipping_for($total) {
  global $CFG;
  if ($total <= 0) return 0;
  return $total >= $CFG['free_shipping_from'] ? 0 : (int)$CFG['shipping_cost'];
}

/* ---------- ۱۲) متفرقه ---------- */
function order_code() {
  return 'BP-' . date('ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
}
function excerpt($s, $len = 120) {
  $s = strip_tags($s);
  return mb_strlen($s, 'UTF-8') > $len ? mb_substr($s, 0, $len, 'UTF-8') . '…' : $s;
}
function status_label($s) {
  $m = ['ready' => 'آماده سرپرستی', 'reserved' => 'رزرو شده', 'adopted' => 'سرپرست پیدا کرده'];
  return isset($m[$s]) ? $m[$s] : $s;
}
function active_if($cond) { return $cond ? ' aria-current="page"' : ''; }
function fa_date($sqlDate) {
  /* تبدیل ساده میلادی به شمسی بدون کتابخونه بیرونی */
  $ts = strtotime($sqlDate);
  if (!$ts) return '';
  list($gy, $gm, $gd) = explode('-', date('Y-n-j', $ts));
  $g_d_m = [0,31,59,90,120,151,181,212,243,273,304,334];
  $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
  $days = 355666 + (365 * $gy) + ((int)(($gy2 + 3) / 4)) - ((int)(($gy2 + 99) / 100))
        + ((int)(($gy2 + 399) / 400)) + $gd + $g_d_m[$gm - 1];
  $jy = -1595 + (33 * ((int)($days / 12053)));
  $days %= 12053;
  $jy += 4 * ((int)($days / 1461));
  $days %= 1461;
  if ($days > 365) { $jy += (int)(($days - 1) / 365); $days = ($days - 1) % 365; }
  if ($days < 186) { $jm = 1 + (int)($days / 31); $jd = 1 + ($days % 31); }
  else { $jm = 7 + (int)(($days - 186) / 30); $jd = 1 + (($days - 186) % 30); }
  $names = ['فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور',
            'مهر','آبان','آذر','دی','بهمن','اسفند'];
  return fa_num($jd) . ' ' . $names[$jm - 1] . ' ' . fa_num($jy);
}
