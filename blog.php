<?php
require_once __DIR__ . '/inc/functions.php';

$tag   = isset($_GET['tag']) ? clean($_GET['tag'], 40) : '';
$sql   = 'SELECT * FROM articles WHERE active = 1';
$par   = [];
if ($tag !== '') { $sql .= ' AND tag = ?'; $par[] = $tag; }
$sql  .= ' ORDER BY created_at DESC';
$posts = all($sql, $par);
$tags  = all('SELECT tag, COUNT(*) AS n FROM articles WHERE active = 1 GROUP BY tag');
$bpets = all('SELECT p.*, COUNT(a.id) n FROM pets p
              JOIN articles a ON a.pet_id = p.id AND a.active = 1
              GROUP BY p.id ORDER BY p.sort_order');

$NAV = 'blog';
$PAGE_TITLE = 'مجله بانی‌پت — ' . $CFG['site_name'];
$PAGE_DESC  = 'نکته‌های ساده نگهداری حیوون خونگی، از تجربه واقعی.';
require __DIR__ . '/inc/header.php';
?>

<section class="pagehead">
  <div class="dust" aria-hidden="true"></div>
  <span class="blob f2" style="width:120px;height:120px;top:-30px;right:12%;background:#fff;opacity:.5"></span>
  <div class="wrap">
    <div class="crumb"><a href="<?= url('index.php') ?>">خانه</a> ← مجله</div>
    <h1 class="h2">مجله بانی‌پت</h1>
    <p class="lead">چیزهایی که کاش قبل از اولین حیوون خونگی می‌دونستیم</p>
  </div>
</section>

<div class="wrap">
  <div class="layout">
    <aside class="side">
      <h3>موضوع‌ها</h3>
      <ul>
        <li><a href="<?= url('blog.php') ?>"<?= active_if($tag === '') ?>>همه<small><?= fa_num(count(all('SELECT id FROM articles WHERE active=1'))) ?></small></a></li>
        <?php foreach ($tags as $t): ?>
          <li><a href="<?= url('blog.php?tag=' . urlencode($t['tag'])) ?>"<?= active_if($tag === $t['tag']) ?>>
            <span><?= e($t['tag']) ?></span><small><?= fa_num($t['n']) ?></small></a></li>
        <?php endforeach; ?>
      </ul>
    </aside>

    <div>
      <div class="chiprow">
        <?php foreach ($bpets as $bp): ?>
          <a class="chip" href="<?= url('pet.php?slug=' . urlencode($bp['slug'])) ?>">
            <?= e($bp['emoji']) ?> راهنمای <?= e($bp['title']) ?> <small><?= fa_num($bp['n']) ?></small></a>
        <?php endforeach; ?>
      </div>
      <div class="maggrid two">
        <?php foreach ($posts as $a): ?>
          <a class="post reveal" href="<?= url('article.php?slug=' . urlencode($a['slug'])) ?>">
            <div class="cover <?= e($a['tone']) ?>" aria-hidden="true">
              <?= $a['tone'] === 'blue' ? '🐾' : '🐰' ?>
            </div>
            <div class="body">
              <div class="meta">
                <b><?= e($a['tag']) ?></b>
                <span><?= fa_num($a['read_min']) ?> دقیقه</span>
                <span><?= e(fa_date($a['created_at'])) ?></span>
              </div>
              <h3><?= e($a['title']) ?></h3>
              <p><?= e(excerpt($a['excerpt'], 110)) ?></p>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>
