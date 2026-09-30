<?php
/* نقشه سایت — گوگل از همین فایل همه صفحه‌ها رو پیدا می‌کنه
   آدرسش: https://دامنه‌ات/sitemap.xml  (یا sitemap.php اگه rewrite نداشتی) */
require_once __DIR__ . '/inc/functions.php';
header('Content-Type: application/xml; charset=utf-8');

$urls = [
  ['index.php', '1.0', 'daily'],
  ['adopt.php', '0.9', 'daily'],
  ['shop.php', '0.9', 'daily'],
  ['blog.php', '0.8', 'weekly'],
  ['rahnama.php?pet=khargoosh', '0.9', 'monthly'],
  ['about.php', '0.5', 'monthly'],
  ['faq.php', '0.6', 'monthly'],
  ['contact.php', '0.5', 'monthly'],
];
foreach (all('SELECT slug FROM pets ORDER BY sort_order') as $r) {
  $urls[] = ['pet.php?slug=' . $r['slug'], '0.8', 'weekly'];
}
foreach (all('SELECT slug FROM categories ORDER BY sort_order') as $r) {
  $urls[] = ['shop.php?cat=' . $r['slug'], '0.7', 'weekly'];
}
foreach (all('SELECT slug FROM products WHERE active = 1') as $r) {
  $urls[] = ['product.php?slug=' . $r['slug'], '0.7', 'weekly'];
}
foreach (all("SELECT slug FROM animals WHERE active = 1 AND status <> 'adopted'") as $r) {
  $urls[] = ['animal.php?slug=' . $r['slug'], '0.8', 'daily'];
}
foreach (all("SELECT guide, slug FROM guide_sections WHERE active = 1 AND slug <> ''") as $r) {
  $urls[] = ['rahnama.php?pet=' . $r['guide'] . '&b=' . $r['slug'], '0.7', 'monthly'];
}
foreach (all('SELECT slug, created_at FROM articles WHERE active = 1') as $r) {
  $urls[] = ['article.php?slug=' . $r['slug'], '0.7', 'monthly', $r['created_at']];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
  $mod = isset($u[3]) ? date('Y-m-d', strtotime($u[3])) : date('Y-m-d');
  echo "  <url>\n";
  echo '    <loc>' . htmlspecialchars(url($u[0]), ENT_XML1) . "</loc>\n";
  echo '    <lastmod>' . $mod . "</lastmod>\n";
  echo '    <changefreq>' . $u[2] . "</changefreq>\n";
  echo '    <priority>' . $u[1] . "</priority>\n";
  echo "  </url>\n";
}
echo '</urlset>';
