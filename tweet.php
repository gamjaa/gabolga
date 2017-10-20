<?php
require 'twitteroauth/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;
require 'config.php';

session_start();

// MySQL 데이터베이스 연결
$mysqli = new mysqli('localhost', DB_ID, DB_PW, 'gabolga');

// 연결 오류 발생 시 스크립트 종료
if ($mysqli->connect_errno) {
    die('Connect Error: '.$mysqli->connect_error);
}

$pattern = '/[^0-9]+/';

if ($_GET['id']) {
  $gabolga_id = preg_replace($pattern, "", $_GET['id']);
  $query = "SELECT tweet_id FROM tweet WHERE gabolga_id = {$gabolga_id}";
  if ($result = $mysqli->query($query)) {
    $data = $result->fetch_assoc();
    $result->free();
  }
  $tweet_id = $data['tweet_id'];
}
else if(isset($_GET['tweet_id'])) {
  $tweet_id = preg_replace($pattern, "", $_GET['tweet_id']);
} else if (isset($_GET['url'])) {
  $url = parse_url($_GET['url']);
  $tweet_url = explode("/", $url['path']);
  if ($url['host'] == "twitter.com" && $tweet_url[2] == "status") {
    $tweet_id = preg_replace($pattern, "", $tweet_url[3]);
  } else {
    header("HTTP/1.0 400");
    die("잘못된 접근입니다.");
  }
} else {
  header("HTTP/1.0 400");
  die("잘못된 접근입니다.");
}

$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
$oembed = $connection->get("statuses/oembed", ["id" => $tweet_id, "hide_media" => "true", "hide_thread" => "true", "lang" => "ko"]);

if(isset($oembed->errors)) {
  header("HTTP/1.0 400");
  die("잘못된 접근입니다.");
}

if (isset($_SESSION['access_token'])) {
  $access_token = $_SESSION['access_token'];
  //$query = "SELECT tweet.tweet_id, name, address, road_address, phone, lat, lng, my_map.user_id FROM tweet LEFT JOIN my_map ON my_map.tweet_id = tweet.tweet_id WHERE tweet.tweet_id = {$tweet_id} AND (my_map.user_id = '{$access_token['user_id']}' OR my_map.user_id IS NULL)";
}

$query = "SELECT tweet_id, name, address, road_address, phone, lat, lng, image_url FROM tweet WHERE tweet_id = {$tweet_id}";
if ($result = $mysqli->query($query)) {
  $data = $result->fetch_assoc();
  $result->free();
}

$query = "SELECT tweet_id FROM my_map WHERE user_id = {$access_token['user_id']} AND tweet_id = {$tweet_id}";
if ($result = $mysqli->query($query)) {
  $my_map = $result->fetch_assoc();
  $result->free();
}

$mysqli->close();

$page_title = isset($data['name'])?$data['name']:"미등록된 트윗";
include_once("header.php");
?>
<div class="modal fade" id="select_place" tabindex="-1" role="dialog" aria-labelledby="select_place_label" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="select_place_label">장소 등록</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <span id="select_place_name"></span>(으)로 등록하시겠습니까?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">아니요</button>
        <button type="button" class="btn btn-primary" id="select_place_submit">네</button>
      </div>
    </div>
  </div>
</div>

<div id="map" style="width:100%; height:calc(100vh - 56px);">
  <div id="map_pop">
    <script>var tweet = false;</script>

    <button id="tweet_switch" onclick="tweet_switch(tweet);" class="btn btn-sm btn-primary" style="position: absolute; z-index: 3;">ㅡ</button>

    <div id="tweet">
      <?=$oembed->html?>
    </div>
    <div id="padding"></div>

    <script>
    document.body.scrollTop = 0;
    if(window.innerHeight < 600) {
      tweet = false;
      tweet_switch(tweet);
    }
    $(window).resize(function() {
      document.body.scrollTop = 0;
      if(window.innerHeight < 600) {
        tweet = false;
        tweet_switch(tweet);
      } else {
        tweet = true;
        tweet_switch(tweet);
      }
    });
    function tweet_switch(i) {
      if(i) {
        $("#tweet").css("display", "block");
        $("#tweet_switch").text("ㅡ");
        $("#padding").css("padding-bottom", "0");
        tweet = false;
      } else {
        $("#tweet").css("display", "none");
        $("#tweet_switch").text("ㅁ");
        $("#padding").css("padding-bottom", "30px");
        tweet = true;
      }
    }
    </script>

    <?=(!isset($data['name']) && !isset($access_token))?"":"<!--"?>
    <div class="alert alert-primary center" role="alert" >
      <a href="sign-in.php?url=<?=$_SERVER['REQUEST_URI']?>">로그인해서 장소를 등록해주세요 ;^;</a>
    </div>
    <?=(!isset($data['name']) && !isset($access_token))?"":"-->"?>

    <div id="data" style="width:100%;">
      <?=isset($data['name'])?"":"<!--"?>
      <div id="gabolga">
        <h3><?=$data['name']?></h3>
        <?php
        if($data['road_address'] != "") {
          echo $data['road_address']." <button class=\"btn btn-link btn-sm copy\" data-clipboard-text=\"".$data['road_address']."\">클립보드에 복사</button><br>";
          echo "(".$data['address'].") <button class=\"btn btn-link btn-sm copy\" data-clipboard-text=\"".$data['address']."\">클립보드에 복사</button><br>";
        } else {
          echo $data['address']." <button class=\"btn btn-link btn-sm copy\" data-clipboard-text=\"".$data['address']."\">클립보드에 복사</button><br>";
        }

        if($data['phone'] != "") {
          echo $data['phone']." <button class=\"btn btn-link btn-sm copy\" data-clipboard-text=\"".$data['phone']."\">클립보드에 복사</button><br>";
        }

        if(isset($my_map)) {
          echo "<br>
          <button id=\"btn_gabolga\" class=\"btn btn-danger\" onclick=\"add_remove()\">가볼가 취소</button>";
        } else if(isset($access_token)) {
          echo "<br>
          <button id=\"btn_gabolga\" class=\"btn btn-primary\" onclick=\"add_remove()\">가볼가 하기</button>";
        } else {
          echo "<div class=\"alert alert-primary center\" role=\"alert\" >
            <a href=\"sign-in.php?url={$_SERVER['REQUEST_URI']}\">로그인해서 내 지도에 등록해보세요!</a>
          </div>";
        }
        ?>
      </div>
      <?=isset($data['name'])?"":"-->"?>

      <?=(!isset($data['name']) && isset($access_token))?"":"<!--"?>
      <div class="option">
        <form onsubmit="searchPlaces(); return false;">
          <div class="input-group">
            <input type="text" id="keyword" placeholder="장소명" size="15" class="form-control">
            <span class="input-group-btn">
              <button class="btn btn-primary" type="submit">검색</button>
            </span>
          </div>
        </form>
      </div>
      <ul id="placesList" style="height: 200px; overflow-y: auto;">
      트윗에 해당하는 장소를 등록해주세요 ;^;<br><br>
      1. 트윗 내용에 맞는 장소명을 검색한다.<br>
      2. 해당하는 장소를 리스트나 지도에서 클릭한다.
      </ul>
      <div id="pagination"></div>
      <?=(!isset($data['name']) && isset($access_token))?"":"-->"?>
    </div>
  </div>
<script type="text/javascript" src="//dapi.kakao.com/v2/maps/sdk.js?appkey=ccdf52614c4fdbc279d9aa623ba3dc65&libraries=services"></script>

<?=isset($data['name'])?"<!--":""?>
<script type="text/javascript" src="tweet_no_data.js"></script>
<script>
// 검색 결과 목록과 마커를 표출하는 함수입니다
function displayPlaces(places) {
    var listEl = document.getElementById('placesList'),
    menuEl = document.getElementById('menu_wrap'),
    fragment = document.createDocumentFragment(),
    bounds = new daum.maps.LatLngBounds(),
    listStr = '';
    // 검색 결과 목록에 추가된 항목들을 제거합니다
    removeAllChildNods(listEl);
    // 지도에 표시되고 있는 마커를 제거합니다
    removeMarker();
    for ( var i=0; i<places.length; i++ ) {
        // 마커를 생성하고 지도에 표시합니다
        var placePosition = new daum.maps.LatLng(places[i].y, places[i].x),
            marker = addMarker(placePosition, i),
            itemEl = getListItem(i, places[i]); // 검색 결과 항목 Element를 생성합니다
        // 검색된 장소 위치를 기준으로 지도 범위를 재설정하기위해 LatLngBounds 객체에 좌표를 추가합니다
        bounds.extend(placePosition);
        // 마커와 검색결과 항목에 mouseover 했을때 해당 장소에 인포윈도우에 장소명을 표시합니다 mouseout 했을 때는 인포윈도우를 닫습니다
        (function(marker, position, title, addr, road_addr, tel, x, y) {
            // 마커에 click 이벤트를 등록합니다
            daum.maps.event.addListener(marker, 'click', function() {
              /*$("#select_place_name").text(title);
              $("#select_place").modal('show');
              $("#select_place_submit").click((function(title, addr, road_addr, tel, x, y){
                $.post("tweet-submit.php", {tweet_id: '<?=$tweet_id?>', name: title, address: addr, road_address: road_addr, phone: tel, lat: y, lng: x}, function(data){location.reload(true);});
              })(title, addr, road_addr, tel, x, y));*/
              if(confirm(title + "(으)로 등록하시겠습니까?")) {
                $.post("tweet-submit.php", {tweet_id: '<?=$tweet_id?>', name: title, address: addr, road_address: road_addr, phone: tel, lat: y, lng: x}, function(data){location.reload(true);});
              }
            });
            itemEl.onclick = function () {
              if(confirm(title + "(으)로 등록하시겠습니까?")) {
                $.post("tweet-submit.php", {tweet_id: '<?=$tweet_id?>', name: title, address: addr, road_address: road_addr, phone: tel, lat: y, lng: x}, function(data){location.reload(true);});
              }
            };

            daum.maps.event.addListener(marker, 'mouseover', function() {
                displayInfowindow(marker, null, title);
            });
            daum.maps.event.addListener(marker, 'mouseout', function() {
                infowindow.close();
            });
            itemEl.onmouseover =  function () {
                displayInfowindow(marker, position, title);
            };
            itemEl.onmouseout =  function () {
                infowindow.close();
            };
        })(marker, placePosition, places[i].place_name, places[i].address_name, places[i].road_address_name, places[i].phone, places[i].x, places[i].y);

        fragment.appendChild(itemEl);
    }
    // 검색결과 항목들을 검색결과 목록 Elemnet에 추가합니다
    listEl.appendChild(fragment);
    menuEl.scrollTop = 0;
    // 검색된 장소 위치를 기준으로 지도 범위를 재설정합니다
    map.setBounds(bounds);
}
</script>
<?=isset($data['name'])?"-->":""?>

<?=isset($data['name'])?"":"<!--"?>
<script>
new Clipboard('.copy');
var container = document.getElementById('map');
var options = {
  center: new daum.maps.LatLng(<?=$data['lat']?>, <?=$data['lng']?>),
  level: 3
};
var map = new daum.maps.Map(container, options);
var marker = new daum.maps.Marker({
    map: map, position: new daum.maps.LatLng(<?=$data['lat']?>, <?=$data['lng']?>)
});

function add_remove() {
  $.get("add-remove.php?tweet_id=<?=$tweet_id?>", function(data) {
    if($("#btn_gabolga").text() == "가볼가 하기") {
      $("#btn_gabolga").text("가볼가 취소");
      $("#btn_gabolga").addClass("btn-danger");
      $("#btn_gabolga").removeClass("btn-primary");
    } else {
      $("#btn_gabolga").text("가볼가 하기");
      $("#btn_gabolga").addClass("btn-primary");
      $("#btn_gabolga").removeClass("btn-danger");
    }
  });
}
</script>
<?=isset($data['name'])?"":"-->"?>
</div>
</body>
</html>
