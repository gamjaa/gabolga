<?php
session_start();
require 'config.php';

if(isset($_SESSION['access_token'])) {
  $access_token = $_SESSION['access_token'];
} else {
  header("Location: sign-in.php?url=".$_SERVER['REQUEST_URI']);
}

$page_title = "내 지도(목록으로 보기)";
include_once("header.php");

// MySQL 데이터베이스 연결
$mysqli = new mysqli('localhost', DB_ID, DB_PW, 'gabolga');

// 연결 오류 발생 시 스크립트 종료
if ($mysqli->connect_errno) {
    die('Connect Error: '.$mysqli->connect_error);
}

$query = "SELECT my_map.tweet_id, name, road_address, address, phone, add_time FROM my_map LEFT JOIN tweet ON my_map.tweet_id = tweet.tweet_id WHERE user_id = {$access_token['user_id']} ORDER BY tweet.road_address DESC";
$count = 0;
?>
<div class="container">
  <h3>
    내 지도(목록으로 보기)
    <small class="text-muted">가볼가 한 장소를 목록으로 봅니다.</small>
  </h3>
  <table id="list" class="table table-hover">
  <thead>
    <tr>
      <th>#</th>
      <th>이름</th>
      <th class="mobile">주소</th>
      <th class="mobile">전화번호</th>
      <th class="mobile">추가일</th>
      <th>-</th>
    </tr>
  </thead>
  <tbody>
  <?php
  if ($result = $mysqli->query($query)) {
    while($data = $result->fetch_assoc()) {
      $count++;
      if($data['name'] != "") {
        echo "<tr>
          <th onclick=\"link('{$data['tweet_id']}')\" scope=\"row\">{$count}</th>
          <td onclick=\"link('{$data['tweet_id']}')\">{$data['name']}</td>";
        if($data['road_address'] != "") {
          echo "<td class=\"mobile\" onclick=\"link('{$data['tweet_id']}')\">{$data['road_address']}({$data['address']})</td>";
        } else {
          echo "<td class=\"mobile\" onclick=\"link('{$data['tweet_id']}')\">{$data['address']}</td>";
        }
        echo "<td class=\"mobile\" onclick=\"link('{$data['tweet_id']}')\">".($data['phone']!=""?$data['phone']:'-')."</td>
          <td class=\"mobile\" onclick=\"link('{$data['tweet_id']}')\">{$data['add_time']}</td>
          <td><button id=\"{$data['tweet_id']}\" class=\"btn btn-danger list_btn\" onclick=\"add_remove('{$data['tweet_id']}')\">가볼가 취소</button></td>
        </tr>";
      } else {
        echo "<tr>
          <th onclick=\"link('{$data['tweet_id']}')\" scope=\"row\">{$count}</th>
          <td onclick=\"link('{$data['tweet_id']}')\" colspan=\"4\">장소 정보를 등록해주세요 ;^;</td>
          <td><button id=\"{$data['tweet_id']}\" class=\"btn btn-danger list_btn\" onclick=\"add_remove('{$data['tweet_id']}')\">가볼가 취소</button></td>
        </tr>";
      }
    }
    $result->free();
  }
  $mysqli->close();
  if($count == 0) {
    echo "<tr>
      <td colspan=\"6\">앗! 가볼가 한 트윗이 없어요!</td>
    </tr>";
  }
  ?>
  <script>
  function add_remove(tweet_id) {
    $.get("add-remove.php?tweet_id="+tweet_id, function(data) {
      if($("#"+tweet_id).text() == "가볼가 하기") {
        $("#"+tweet_id).text("가볼가 취소");
        $("#"+tweet_id).addClass("btn-danger");
        $("#"+tweet_id).removeClass("btn-primary");
      } else {
        $("#"+tweet_id).text("가볼가 하기");
        $("#"+tweet_id).addClass("btn-primary");
        $("#"+tweet_id).removeClass("btn-danger");
      }
    });
  }
  function link(tweet_id) {
    window.open("tweet.php?tweet_id="+tweet_id,"","");
  }
  </script>
  </tbody>
</table>
</div>
</body>
</html>
