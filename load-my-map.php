<?php
session_start();
require 'config.php';

if(isset($_SESSION['access_token'])) {
  $access_token = $_SESSION['access_token'];
} else {
  header("HTTP/1.0 400");
  die("잘못된 접근입니다.");
}

if(isset($_GET['swLat']) && isset($_GET['swLng']) && isset($_GET['neLat']) && isset($_GET['neLng'])) {
  $latlng['swLat'] = floatval($_GET['swLat']);
  $latlng['swLng'] = floatval($_GET['swLng']);
  $latlng['neLat'] = floatval($_GET['neLat']);
  $latlng['neLng'] = floatval($_GET['neLng']);
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

$query = "SELECT tweet.tweet_id, name, lat, lng FROM my_map JOIN tweet ON my_map.tweet_id = tweet.tweet_id WHERE user_id = ".$access_token['user_id']." AND lat BETWEEN ".$latlng['swLat']." AND ".$latlng['neLat']." AND lng BETWEEN ".$latlng['swLng']." AND ".$latlng['neLng'];

if ($result = $mysqli->query($query)) {
  while($data = $result->fetch_array(MYSQLI_ASSOC)) {
    $var[] = $data;
  }
  $result->free();
}

$mysqli->close();

echo json_encode($var, JSON_UNESCAPED_UNICODE);
?>
