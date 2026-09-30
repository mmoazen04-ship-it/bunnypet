<?php
/* راهنمای نگهداری
   بدون پارامتر b  →  صفحه فهرست
   با پارامتر b    →  صفحه همون بخش */
if (isset($_GET['b']) && $_GET['b'] !== '') {
  require __DIR__ . '/rahnama-one.php';
} else {
  require __DIR__ . '/rahnama-list.php';
}
