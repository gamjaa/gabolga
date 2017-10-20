<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>가볼가 :: <?=$page_title ?></title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/gabolga.css">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php
  if ($_SERVER['PHP_SELF']=='/index.php')
    $desc = "가볼까 싶은 트위터 맛집들, '가볼가'가 대신 정리해드립니다!";
  else if ($_SERVER['PHP_SELF']=='/tweet.php') {
    $desc = isset($data)?"{$data['road_address']}({$data['address']})":"장소를 등록해주세요 ;^;";
  } else {$desc = "";}

  /*if ($_SERVER['PHP_SELF']=='/tweet.php') {
    $image = $data['image_url'];
  }
  else {$image = "https://gabolga.gamjabox.kr/gabolga_w.png";}*/
  ?>
  <meta name="twitter:card" content="summary">
  <meta name="twitter:site" content="@GABOLGA_bot">
  <meta name="twitter:title" content="가볼가 :: <?=$page_title ?>">
  <meta name="twitter:description" content="<?=$desc?>">
  <meta name="twitter:image" content="https://gabolga.gamjaa.com/gabolga_w.png">
  <meta name="og:title" content="가볼가 :: <?=$page_title ?>">
  <meta name="og:description" content="<?=$desc?>">
  <meta name="og:image" content="https://gabolga.gamjaa.com/gabolga_w.png">
</head>
<body <?=($_SERVER['PHP_SELF']=='/my-map.php' || $_SERVER['PHP_SELF']=='/tweet.php')?"class=\"include_map\"":""?>>
  <?php include_once("analyticstracking.php"); ?>
  <script src="js/jquery-3.2.1.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <?=($_SERVER['PHP_SELF']=='/tweet.php' || $_SERVER['PHP_SELF']=='/new-tweet.php')?"<script src=\"js/clipboard.min.js\"></script>":""?>

  <nav class="navbar navbar-light navbar-expand-lg sticky-top" style="background-color: #e3f2fd;">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
    </button>

      <a href="/" class="navbar-brand">가볼가</a>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav mr-auto mt-2 mt-lg-0">
        <li class="nav-item <?=$_SERVER['PHP_SELF']=='/index.php'?'active':''?>">
          <a class="nav-link" href="/">홈</a>
        </li>
        <li class="nav-item <?=$_SERVER['PHP_SELF']=='/my-map.php'?'active':''?>">
          <a class="nav-link" href="my-map.php" title="가볼가 한 장소를 지도로 모아보세요!">내 지도</a>
        </li>
        <li class="nav-item <?=$_SERVER['PHP_SELF']=='/my-list.php'?'active':''?>">
          <a class="nav-link" href="my-list.php" title="가볼가 한 장소를 목록으로 모아보세요!">(목록으로 보기)</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-dark" href="random.php" title="등록된 트윗을 랜덤으로 보여드립니다!">랜덤 트윗</a>
        </li>
        <li class="nav-item <?=$_SERVER['PHP_SELF']=='/new-tweet.php'?'active':''?>">
          <a class="nav-link" href="new-tweet.php" title="트윗과 함께 위치 정보를 공유해보세요!">링크 생성</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="https://twitter.com/GABOLGA_bot" target="_blank" title="DM으로 편하게 등록하세요!">@GABOLGA_bot(새 창)</a>
        </li>
      </ul>

      <form action="tweet.php" class="form-inline">
        <div class="input-group">
          <input type="url" name="url" class="form-control" required placeholder="트윗 링크" aria-label="트윗 링크">
          <span class="input-group-btn">
            <button class="btn btn-outline-primary" type="submit">장소 찾기</button>
          </span>
        </div>
      </form>
    </div>
  &nbsp;&nbsp;&nbsp;&nbsp;
  <?php
  session_start();
  if(isset($_SESSION['access_token'])) {
    $access_token = $_SESSION['access_token'];

    echo "<button onclick=\"location.href='logout.php".(($_SERVER['PHP_SELF']=='/tweet.php')?('?url='.$_SERVER['REQUEST_URI']):'')."'\" class=\"btn btn-outline-success\" type=\"button\">로그아웃</button>
    ";
  } else {
    echo "
    <a href=\"sign-in.php?url={$_SERVER['REQUEST_URI']}\" alt=\"Sign in with Twitter\"><img src=\"https://g.twimg.com/dev/sites/default/files/images_documentation/sign-in-with-twitter-gray.png\"></a>
    ";
  }
  ?>
  </nav>
