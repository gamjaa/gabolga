<?php
session_start();
require 'config.php';

if(isset($_SESSION['access_token'])) {
  $access_token = $_SESSION['access_token'];
} else {
  header("Location: sign-in.php?url=".$_SERVER['REQUEST_URI']);
}

$page_title = "링크 생성";
include_once("header.php");

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
  // 기존에 생성해둔 게 없을 때: 생성
  $query = "INSERT INTO tweet (writer) VALUES ({$access_token['user_id']})";
  $mysqli->query($query);
  $gabolga_id = $mysqli->insert_id;
}
?>
<script>new Clipboard('.copy');</script>
<div class="container">
  <h3>
    링크 생성
    <small class="text-muted">작성 중인 트윗에 넣을 링크를 생성합니다.</small>
  </h3>
  <form action="new-tweet-submit.php" method="post">
    <div class="form-group row">
      <label for="gabolga" class="col-2 col-form-label">가볼가 주소</label>
      <input type="url" class="form-control col-5" id="gabolga" value="https://gabolga.gamjaa.com/tweet.php?id=<?=$gabolga_id?>" readonly>
      <button type="button" class="btn btn-primary copy" data-clipboard-text="https://gabolga.gamjaa.com/tweet.php?id=<?=$gabolga_id?>">복사하기</button>
    </div>
    <div class="form-group row">
      <label class="col-2 col-form-label"></label>
      <small class="text-muted">
        위의 주소로 위치를 확인하게 됩니다. 작성 중인 트윗에 붙여넣기 하세요.
      </small>
    </div>
    <div class="form-group row">
      <label for="tweet" class="col-2 col-form-label">트윗 주소</label>
      <input type="url" class="form-control col-6" name="tweet" placeholder="https://twitter.com/GABOLGA_bot/status/905073894515556352" required>
    </div>
    <div class="form-group row">
      <label class="col-2 col-form-label"></label>
      <small class="text-muted">
        트윗하기를 완료하고 등록된 트윗의 링크 주소를 복사해 붙여넣기 하세요.
      </small>
    </div>
    <button type="submit" class="btn btn-primary">위치 등록하러 가기</button>
  </form>
</div>
