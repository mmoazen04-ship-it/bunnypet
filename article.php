<?php
require_once __DIR__ . '/inc/functions.php';

$slug = isset($_GET['slug']) ? clean($_GET['slug'], 90) : '';
$a = one('SELECT * FROM articles WHERE slug = ? AND active = 1', [$slug]);
if (!$a) { http_response_code(404); }
else { q('UPDATE articles SET views = views + 1 WHERE id = ?', [$a['id']]); }

$NAV = 'blog';
$PAGE_TITLE = ($a ? $a['title'] : 'پیدا نشد') . ' — ' . $CFG['site_name'];
$PAGE_DESC  = $a ? $a['excerpt'] : '';
if ($a) {
  $CANON = url('article.php?slug=' . $a['slug']);
  $JSONLD = [
    '@context' => 'https://schema.org', '@type' => 'Article',
    'headline' => $a['title'], 'description' => $a['excerpt'],
    'inLanguage' => 'fa-IR',
    'datePublished' => date('c', strtotime($a['created_at'])),
    'author' => ['@type' => 'Organization', 'name' => $CFG['site_name']],
    'publisher' => ['@type' => 'Organization', 'name' => $CFG['site_name']],
    'mainEntityOfPage' => $CANON,
  ];
}
require __DIR__ . '/inc/header.php';
?>

<?php if (!$a): ?>
  <div class="empty">
    <div class="big">📄</div>
    <h1 class="h2">این مقاله پیدا نشد</h1>
    <a class="btn" href="<?= url('blog.php') ?>">برگرد به مجله</a>
  </div>
<?php else: ?>

<section class="pagehead">
  <div class="dust" aria-hidden="true"></div>
  <div class="wrap">
    <div class="crumb">
      <a href="<?= url('index.php') ?>">خانه</a> ←
      <a href="<?= url('blog.php') ?>">مجله</a> ←
      <a href="<?= url('blog.php?tag=' . urlencode($a['tag'])) ?>"><?= e($a['tag']) ?></a>
    </div>
  </div>
</section>

<article class="article">
  <h1><?= e($a['title']) ?></h1>
  <div class="meta">
    <?= e(fa_date($a['created_at'])) ?> ·
    <?= fa_num($a['read_min']) ?> دقیقه خواندن ·
    <?= fa_num($a['views']) ?> بازدید
  </div>
  <div class="content">
    <?= $a['body'] /* محتوای مقاله از پنل مدیریت، فقط مدیر می‌تونه بنویسه */ ?>
  </div>

  <div class="summary" style="position:static;margin-top:30px;text-align:center">
    <h3 class="h3">سؤالی برات مونده؟</h3>
    <p class="lead">شماره‌ت رو بذار، یه تماس کوتاه می‌گیریم و راهنماییت می‌کنیم. رایگانه.</p>
    <a class="btn" style="margin-top:12px" href="<?= url('index.php#join') ?>">درخواست مشاوره</a>
  </div>
</article>

<?php
$rel = all('SELECT * FROM articles WHERE id <> ? AND active = 1 ORDER BY created_at DESC LIMIT 3',
           [$a['id']]);
if ($rel): ?>
  <section class="mag">
    <div class="wrap">
      <div class="sechead"><h2 class="h3">اینا رو هم بخون</h2></div>
      <div class="maggrid">
        <?php foreach ($rel as $r): ?>
          <a class="post reveal" href="<?= url('article.php?slug=' . urlencode($r['slug'])) ?>">
            <div class="cover <?= e($r['tone']) ?>" aria-hidden="true">
              <?= $r['tone'] === 'blue' ? '🐾' : '🐰' ?>
            </div>
            <div class="body">
              <div class="meta"><b><?= e($r['tag']) ?></b></div>
              <h3><?= e($r['title']) ?></h3>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php endif; ?>
<?php require __DIR__ . '/inc/footer.php'; ?>
