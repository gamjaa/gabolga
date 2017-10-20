<?php
session_start();
require 'config.php';

if(isset($_SESSION['access_token'])) {
  $access_token = $_SESSION['access_token'];
} else {
  header("Location: sign-in.php?url=".$_SERVER['REQUEST_URI']);
}

if (isset($_POST['tweet'])) {
  $url = parse_url($_POST['tweet']);
  $tweet_url = explode("/", $url['path']);
  if ($url['host'] == "twitter.com" && $tweet_url[2] == "status") {
    $tweet_id = preg_replace('/[^0-9]+/', "", $tweet_url[3]);
  } else {
    header("HTTP/1.0 400");
    die("잘못된 접근입니다.");
  }
} else {
  header("HTTP/1.0 400");
  die("잘못된 접근입니다.");
}

// MySQL 데이터베이스 연결
$mysqli = new mysqli('localhost', DB_ID, DB_PW, 'gabolga');

// 연결 오류 발생 시 스크립트 종료
if ($mysqli->connect_errno) {
    die('Connect Error: '.$mysqli->connect_error);
}

$query = "SELECT gabolga_id FROM tweet WHERE writer = {$access_token['user_id']} AND tweet_id IS NULL";

if ($result = $mysqli->query($query)) {
  $data = $result->fetch_assoc();
  $result->free();
}

if (isset($data['gabolga_id'])) {
  // 기존에 생성해둔 게 있을 때: 사용
  $gabolga_id = $data['gabolga_id'];
} else {
  echo "<a href='new-tweet.php'>다시 링크를 생성해주세요.</a><br>";
  die("오류 발생: 해당 가볼가 주소 없음");
}

$query = "UPDATE tweet SET tweet_id = {$tweet_id} WHERE gabolga_id = {$gabolga_id}";
$mysqli->query($query);

header("Location: tweet.php?tweet_id={$tweet_id}");
?>
