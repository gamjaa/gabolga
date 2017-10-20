<?php
// MySQL 데이터베이스 연결
require 'config.php';
$mysqli = new mysqli('localhost', DB_ID, DB_PW, 'gabolga');

// 연결 오류 발생 시 스크립트 종료
if ($mysqli->connect_errno) {
    die('Connect Error: '.$mysqli->connect_error);
}

$query = "SELECT tweet_id FROM tweet WHERE name IS NOT NULL";
$result = $mysqli->query($query);
$tweet_id = array();
while($data = $result->fetch_assoc()) {
  $tweet_id[] = $data['tweet_id'];
}
$result->free();
$mysqli->close();

header("Location: tweet.php?tweet_id={$tweet_id[mt_rand(0, count($tweet_id)-1)]}");
?>
