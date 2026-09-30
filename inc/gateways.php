<?php
/* ==========================================================================
   لایه درگاه پرداخت
   اضافه کردن درگاه جدید = یه بلوک توی config.php + یه case اینجا. همین.
   تا وقتی $CFG['sandbox'] روی true باشه، هیچ تراکنش واقعی انجام نمی‌شه و
   یه صفحه شبیه‌سازی نشون داده می‌شه که کارفرما بتونه کل مسیر رو تست کنه.
   ========================================================================== */

function gw_list() {
  global $CFG;
  $out = [];
  foreach ($CFG['gateways'] as $key => $g) {
    if (!empty($g['enabled'])) { $g['key'] = $key; $out[$key] = $g; }
  }
  return $out;
}

function gw_http_post($url, $payload) {
  $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
  if (function_exists('curl_init')) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => $json,
      CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
      CURLOPT_TIMEOUT => 20,
      CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
  } else {
    $ctx = stream_context_create(['http' => [
      'method' => 'POST', 'header' => "Content-Type: application/json\r\n",
      'content' => $json, 'timeout' => 20,
    ]]);
    $res = @file_get_contents($url, false, $ctx);
  }
  return $res ? json_decode($res, true) : null;
}

/* ---------- شروع پرداخت: یا آدرس درگاه برمی‌گردونه یا پیام خطا ---------- */
function gw_start($order) {
  global $CFG;
  $key = $order['gateway'];
  $gws = gw_list();
  if (!isset($gws[$key])) return ['err' => 'این روش پرداخت فعال نیست'];
  $g = $gws[$key];

  /* روش‌های بدون درگاه آنلاین */
  if ($key === 'cod' || $key === 'card') {
    q("UPDATE orders SET status = 'awaiting' WHERE id = ?", [$order['id']]);
    return ['done' => true];
  }

  /* حالت آزمایشی */
  if (!empty($CFG['sandbox'])) {
    return ['sandbox' => true];
  }

  $callback = BASE_URL . '/callback.php?code=' . urlencode($order['code']);
  $amount   = (int)$order['total'];

  if ($key === 'zarinpal') {
    $res = gw_http_post('https://payment.zarinpal.com/pg/v4/payment/request.json', [
      'merchant_id'  => $g['merchant'],
      'amount'       => $amount * 10,   /* زرین‌پال ریال می‌گیره */
      'callback_url' => $callback,
      'description'  => 'سفارش ' . $order['code'],
      'metadata'     => ['mobile' => $order['phone']],
    ]);
    if (isset($res['data']['authority']) && $res['data']['authority'] !== '') {
      q('UPDATE orders SET ref_id = ? WHERE id = ?', [$res['data']['authority'], $order['id']]);
      return ['go' => 'https://payment.zarinpal.com/pg/StartPay/' . $res['data']['authority']];
    }
    return ['err' => 'درگاه زرین‌پال جواب نداد. چند دقیقه بعد دوباره امتحان کن.'];
  }

  if ($key === 'idpay') {
    /* برای آیدی‌پی هدر جداگانه لازمه */
    $ch = curl_init('https://api.idpay.ir/v1.1/payment');
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_TIMEOUT => 20,
      CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'X-API-KEY: ' . $g['api_key']],
      CURLOPT_POSTFIELDS => json_encode([
        'order_id' => $order['code'], 'amount' => $amount * 10,
        'phone' => $order['phone'], 'callback' => $callback,
      ], JSON_UNESCAPED_UNICODE),
    ]);
    $res = json_decode((string)curl_exec($ch), true);
    curl_close($ch);
    if (!empty($res['link'])) {
      q('UPDATE orders SET ref_id = ? WHERE id = ?', [$res['id'], $order['id']]);
      return ['go' => $res['link']];
    }
    return ['err' => 'درگاه آیدی‌پی جواب نداد'];
  }

  if ($key === 'nextpay') {
    $res = gw_http_post('https://nextpay.org/nx/gateway/token', [
      'api_key' => $g['api_key'], 'order_id' => $order['code'],
      'amount' => $amount * 10, 'callback_uri' => $callback,
    ]);
    if (!empty($res['trans_id'])) {
      q('UPDATE orders SET ref_id = ? WHERE id = ?', [$res['trans_id'], $order['id']]);
      return ['go' => 'https://nextpay.org/nx/gateway/payment/' . $res['trans_id']];
    }
    return ['err' => 'درگاه نکست‌پی جواب نداد'];
  }

  return ['err' => 'روش پرداخت ناشناخته'];
}

/* ---------- تأیید پرداخت بعد از برگشت از بانک ---------- */
function gw_verify($order) {
  global $CFG;
  $key = $order['gateway'];
  $gws = gw_list();
  if (!isset($gws[$key])) return false;
  $g = $gws[$key];

  /* مبلغ همیشه از دیتابیس خونده می‌شه، نه از چیزی که از بانک برمی‌گرده */
  $amount = (int)$order['total'];

  if (!empty($CFG['sandbox'])) {
    return isset($_GET['ok']) && $_GET['ok'] === '1';
  }

  if ($key === 'zarinpal') {
    $auth = isset($_GET['Authority']) ? $_GET['Authority'] : '';
    $st   = isset($_GET['Status']) ? $_GET['Status'] : '';
    if ($st !== 'OK' || $auth === '' || $auth !== $order['ref_id']) return false;
    $res = gw_http_post('https://payment.zarinpal.com/pg/v4/payment/verify.json', [
      'merchant_id' => $g['merchant'], 'amount' => $amount * 10, 'authority' => $auth,
    ]);
    if (isset($res['data']['code']) && in_array((int)$res['data']['code'], [100, 101], true)) {
      q('UPDATE orders SET ref_id = ? WHERE id = ?',
        [isset($res['data']['ref_id']) ? $res['data']['ref_id'] : $auth, $order['id']]);
      return true;
    }
    return false;
  }

  if ($key === 'idpay') {
    $ch = curl_init('https://api.idpay.ir/v1.1/payment/verify');
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_TIMEOUT => 20,
      CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'X-API-KEY: ' . $g['api_key']],
      CURLOPT_POSTFIELDS => json_encode([
        'id' => $order['ref_id'], 'order_id' => $order['code'],
      ]),
    ]);
    $res = json_decode((string)curl_exec($ch), true);
    curl_close($ch);
    return isset($res['status']) && (int)$res['status'] === 100
        && (int)$res['amount'] === $amount * 10;
  }

  if ($key === 'nextpay') {
    $res = gw_http_post('https://nextpay.org/nx/gateway/verify', [
      'api_key' => $g['api_key'], 'trans_id' => $order['ref_id'], 'amount' => $amount * 10,
    ]);
    return isset($res['code']) && (int)$res['code'] === 0;
  }

  return false;
}
