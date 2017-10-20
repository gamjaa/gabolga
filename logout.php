<?php include_once("analyticstracking.php") ?>
<?php
session_start();
session_destroy();
define('SITE', 'https://gabolga.gamjaa.com/');
header('Location: '.SITE);
?>
