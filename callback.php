<?php
session_start();
require 'twitteroauth/autoload.php';
require 'config.php';
use Abraham\TwitterOAuth\TwitterOAuth;

$callback = (isset($_GET['url'])?$_GET['url']:'my-map.php');

if(isset($_SESSION['access_token'])) {
  header("Location: {$callback}");
} else if (isset($_GET['denied'])) {
  header("Location: ".isset($_GET['url'])?$callback:'/');
}
else {
  $request_token = [];
  $request_token['oauth_token'] = $_SESSION['oauth_token'];
  $request_token['oauth_token_secret'] = $_SESSION['oauth_token_secret'];

  if (isset($_REQUEST['oauth_token']) && $request_token['oauth_token'] !== $_REQUEST['oauth_token']) {
      echo "<script>alert('오류 발생!\n다시 진행해주세요!');</script>";
  }

  $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $request_token['oauth_token'], $request_token['oauth_token_secret']);

  $access_token = $connection->oauth("oauth/access_token", ["oauth_verifier" => $_REQUEST['oauth_verifier']]);

  $_SESSION['access_token'] = $access_token;

  // MySQL 데이터베이스 연결
  $mysqli = new mysqli('localhost', DB_ID, DB_PW, 'gabolga');

  // 연결 오류 발생 시 스크립트 종료
  if ($mysqli->connect_errno) {
      die('Connect Error: '.$mysqli->connect_error);
  }

  // 쿼리문 전송
  // user_id, screen_name, oauth_token, oauth_token_secret
  $query = "INSERT INTO users (user_id, screen_name, oauth_token, oauth_token_secret) VALUES ({$access_token['user_id']}, '{$access_token['screen_name']}', '{$access_token['oauth_token']}', '{$access_token['oauth_token_secret']}') ON DUPLICATE KEY UPDATE screen_name='{$access_token['screen_name']}', oauth_token='{$access_token['oauth_token']}', oauth_token_secret='{$access_token['oauth_token_secret']}' ";

  $mysqli->query($query);

  // 접속 종료
  $mysqli->close();

  header("Location: {$callback}");
}
?>
