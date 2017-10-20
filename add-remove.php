<?php
require("config.php");

session_start();
if(isset($_SESSION['access_token'])) {
  $access_token = $_SESSION['access_token'];
} else {
  header("HTTP/1.0 400");
  die("잘못된 접근입니다.");
}

if(isset($_GET['tweet_id'])) {
  $tweet_id = preg_replace('/[^0-9]+/', "", $_GET['tweet_id']);
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

$query = "SELECT * FROM my_map WHERE user_id = {$access_token['user_id']} AND tweet_id = {$tweet_id}";
if ($result = $mysqli->query($query)) {
  $data = $result->fetch_assoc();
  $result->free();
}

if(isset($data)) {
  $query = "DELETE FROM my_map WHERE user_id = {$access_token['user_id']} AND tweet_id = {$tweet_id}";
} else {
  $query = "INSERT INTO my_map (user_id, tweet_id) VALUES ({$access_token['user_id']}, {$tweet_id})";
}

$mysqli->query($query);
$mysqli->close();
?>
