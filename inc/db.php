<?php
/* اتصال به دیتابیس — هم SQLite هم MySQL */

function db() {
  global $CFG;
  static $pdo = null;
  if ($pdo !== null) return $pdo;

  $c = $CFG['db'];
  try {
    if ($c['driver'] === 'mysql') {
      $dsn = "mysql:host={$c['host']};dbname={$c['name']};charset={$c['charset']}";
      $pdo = new PDO($dsn, $c['user'], $c['pass']);
    } else {
      $dir = dirname($c['sqlite_path']);
      if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
      $pdo = new PDO('sqlite:' . $c['sqlite_path']);
      $pdo->exec('PRAGMA foreign_keys = ON');
      $pdo->exec('PRAGMA journal_mode = WAL');
    }
  } catch (PDOException $e) {
    http_response_code(500);
    exit('<div style="font-family:Tahoma;direction:rtl;padding:40px;line-height:2">
      <h2>اتصال به دیتابیس برقرار نشد</h2>
      <p>اگه تازه سایت رو آپلود کردی، اول فایل <b>install.php</b> رو توی مرورگر باز کن.</p>
      <p>اگه از MySQL استفاده می‌کنی، مشخصات دیتابیس توی <b>inc/config.php</b> رو چک کن.</p></div>');
  }

  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
  return $pdo;
}

/* هر کوئری فقط و فقط از این سه تا رد می‌شه — جلوی SQL Injection گرفته می‌شه */
function q($sql, $params = []) {
  $st = db()->prepare($sql);
  $st->execute($params);
  return $st;
}
function one($sql, $params = []) {
  $r = q($sql, $params)->fetch();
  return $r === false ? null : $r;
}
function all($sql, $params = []) {
  return q($sql, $params)->fetchAll();
}
function last_id() {
  return db()->lastInsertId();
}
function db_is_sqlite() {
  global $CFG;
  return $CFG['db']['driver'] !== 'mysql';
}
