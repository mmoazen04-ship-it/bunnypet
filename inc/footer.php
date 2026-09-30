</main>

<footer>
  <div class="dust" aria-hidden="true"></div>
  <svg class="wave" viewBox="0 0 1440 70" preserveAspectRatio="none" aria-hidden="true"
       style="position:absolute;top:0;left:0;z-index:2">
    <path fill="#ffffff" d="M0 0h1440v22c-200 34-420 34-720 14S220 8 0 34z"/>
  </svg>

  <div class="wrap">
    <div class="about">
      <h4><?= e($CFG['site_name']) ?></h4>
      <p>از انتخاب تا نگهداری، کنارتیم. هر چی حیوونت لازم داره، با راهنمایی‌ای که بهش اعتماد داری.</p>
      <div class="trustrow">
        <div class="trustbox">
          <?php if (!empty($CFG['enamad_code'])): ?>
            <?= $CFG['enamad_code'] /* کد رسمی اینماد */ ?>
          <?php else: ?>
            نماد اعتماد<br>الکترونیکی<br><small>در حال دریافت</small>
          <?php endif; ?>
        </div>
        <div class="trustbox">
          <?php if (!empty($CFG['samandehi_code'])): ?>
            <?= $CFG['samandehi_code'] ?>
          <?php else: ?>
            نشان<br>ساماندهی<br><small>در حال دریافت</small>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div>
      <h4>حیوون‌ها</h4>
      <ul>
        <?php foreach (all('SELECT slug, title FROM pets ORDER BY sort_order') as $fc): ?>
          <li><a href="<?= url('pet.php?slug=' . urlencode($fc['slug'])) ?>"><?= e($fc['title']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div>
      <h4>راهنما</h4>
      <ul>
        <li><a href="<?= url('adopt.php') ?>">سرپرستی حیوان</a></li>
        <li><a href="<?= url('shop.php') ?>">فروشگاه لوازم</a></li>
        <li><a href="<?= url('rahnama.php?pet=khargoosh') ?>">راهنمای نگهداری خرگوش</a></li>
        <li><a href="<?= url('blog.php') ?>">مجله بانی‌پت</a></li>
        <li><a href="<?= url('faq.php') ?>">پرسش‌های پرتکرار</a></li>
        <li><a href="<?= url('about.php') ?>">درباره ما</a></li>
        <li><a href="<?= url('account.php') ?>">حساب و سفارش‌ها</a></li>
      </ul>
    </div>

    <div>
      <h4>تماس با ما</h4>
      <ul>
        <li>تلفن: <?= e($CFG['phone']) ?></li>
        <li>موبایل: <?= e($CFG['mobile']) ?></li>
        <li><?= e($CFG['address']) ?></li>
        <li><a href="<?= e($CFG['instagram']) ?>" rel="noopener nofollow" target="_blank">اینستاگرام</a></li>
      </ul>
      <div class="trustrow">
        <?php foreach ($CFG['gateways'] as $fg): if (empty($fg['enabled'])) continue; ?>
          <span class="mark <?= e($fg['logo']) ?>" style="width:54px;height:34px">
            <?= e($fg['logo'] === 'zarinpal' ? 'زرین‌پال' : ($fg['logo'] === 'card' ? 'کارت' :
                ($fg['logo'] === 'cod' ? 'درمحل' : $fg['logo']))) ?>
          </span>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="copybar">
    <div class="wrap">
      <span>© <?= fa_num(date('Y')) ?> <?= e($CFG['site_name']) ?> — همه حقوق محفوظه</span>
      <span>ساخته‌شده برای دوست‌داران حیوونا</span>
    </div>
  </div>
</footer>

<script src="<?= url('assets/js/main.js') ?>" defer></script>
</body>
</html>
