<?php
require 'twitteroauth/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;
require 'config.php';

session_start();
if(isset($_SESSION['access_token'])) {
  $access_token = $_SESSION['access_token'];
} else {
  header("HTTP/1.0 400");
  die("잘못된 접근입니다.");
}

if(isset($_POST['tweet_id']) && isset($_POST['name']) && isset($_POST['address']) && isset($_POST['road_address']) && isset($_POST['phone']) && isset($_POST['lat']) && isset($_POST['lng'])) {
  $post['tweet_id'] = preg_replace('/[^0-9]+/', "", $_POST['tweet_id']);
  $post['name'] = htmlspecialchars($_POST['name']);
  $post['address'] = htmlspecialchars($_POST['address']);
  $post['road_address'] = htmlspecialchars($_POST['road_address']);
  $post['phone'] = htmlspecialchars($_POST['phone']);
  $post['lat'] = preg_replace('/[^0-9.]+/', "", $_POST['lat']);
  $post['lng'] = preg_replace('/[^0-9.]+/', "", $_POST['lng']);
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

$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);
$status = $connection->get("statuses/show", ["id" => $post['tweet_id'], "include_entities" => "true"]);

$query = "INSERT INTO tweet (tweet_id, name, address, road_address, phone, lat, lng, image_url, writer) VALUES ({$post['tweet_id']}, '{$post['name']}', '{$post['address']}', '{$post['road_address']}', '{$post['phone']}', {$post['lat']}, {$post['lng']}, '{$status->entities->media[0]->media_url_https}', {$access_token['user_id']}) ON DUPLICATE KEY UPDATE name='{$post['name']}', address='{$post['address']}', road_address='{$post['road_address']}', phone='{$post['phone']}', lat='{$post['lat']}', lng='{$post['lng']}', image_url='{$status->entities->media[0]->media_url_https}'";

//echo $query;
$mysqli->query($query);
?>
