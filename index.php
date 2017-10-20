<?php
$page_title = "트위터 맛집, 대신 정리해드립니다!";
include_once("header.php");
?>
  <div class="container center">
    <img src="gabolga.png" width="300" height="auto">
    <h3 class="center">가볼까 싶은 트위터 맛집,</h3>
    <h3 class="center">'가볼가'가 대신 정리해드립니다!</h3>
    <br><br>
    <form action="tweet.php" class="row justify-content-center">
      <div class="input-group col-8">
        <input type="url" name="url" class="form-control" required placeholder="https://twitter.com/GABOLGA_bot/status/905073894515556352">
        <span class="input-group-btn">
          <button class="btn btn-primary" type="submit">장소 찾기</button>
        </span>
      </div>
    </form>
    <br><br>
  <button class="btn btn-info" type="button" data-toggle="collapse" data-target="#site" aria-expanded="false" aria-controls="multiCollapseExample1">사용 방법(사이트 편)</button>
  <button class="btn btn-info" type="button" data-toggle="collapse" data-target="#sns" aria-expanded="false" aria-controls="multiCollapseExample1">사용 방법(트위터 편)</button>
</p>
<div class="row">
  <div class="col">
<div class="collapse multi-collapse" id="site">
  <div class="card card-body center">
    <img src="ppt/s1.PNG"><img src="ppt/s2.PNG"><img src="ppt/s3.PNG"><img src="ppt/s4.PNG">
  </div>
</div>
</div>
<div class="col">
<div class="collapse multi-collapse" id="sns">
  <div class="card card-body center">
    <img src="ppt/t1.PNG"><img src="ppt/t2.PNG"><img src="ppt/t3.PNG"><img src="ppt/t4.PNG">
  </div>
</div>
</div>
</div>
  </div>
</body>
</html>
