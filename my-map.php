<?php
session_start();
require 'config.php';

if(isset($_SESSION['access_token'])) {
  $access_token = $_SESSION['access_token'];
} else {
  header("Location: sign-in.php?url=".$_SERVER['REQUEST_URI']);
}

$page_title = "내 지도";
include_once("header.php");

if (isset($_GET['tweet_id'])) {
  $tweet_id = preg_replace('/[^0-9]+/', "", $_GET['tweet_id']);
}

// MySQL 데이터베이스 연결
$mysqli = new mysqli('localhost', DB_ID, DB_PW, 'gabolga');

// 연결 오류 발생 시 스크립트 종료
if ($mysqli->connect_errno) {
    die('Connect Error: '.$mysqli->connect_error);
}

$query = "SELECT lat, lng FROM tweet WHERE tweet_id = {$tweet_id}";
if ($result = $mysqli->query($query)) {
  $data = $result->fetch_assoc();
  $result->free();
}
?>
<div style="position: fixed; top: 70px; left: 10px; width: 50px; height: 50px; z-index: 2;"><a onclick="isWatch = true; getGeo(watchID);"><img src="iconmonstr-crosshair-9-2402.png" width="50px" height="50px"></a></div>

  <div id="map" style="width:100%; height:calc(100vh - 56px);"></div>
  <script type="text/javascript" src="//dapi.kakao.com/v2/maps/sdk.js?appkey=ccdf52614c4fdbc279d9aa623ba3dc65&libraries=services"></script>
  <script type="text/javascript" src="jquery-3.2.1.min.js"></script>
  <script>
		var container = document.getElementById('map');
		var options = {
			center: new daum.maps.LatLng(<?=isset($tweet_id)?$data['lat']:"37.566826"?>, <?=isset($tweet_id)?$data['lng']:"126.9786567"?>),
			level: 4
		};
		var map = new daum.maps.Map(container, options);
    <?=isset($tweet_id)?"/*":""?>
    navigator.geolocation.getCurrentPosition(function(position) {
      map_move(position.coords.latitude, position.coords.longitude);
      lat = position.coords.latitude;
      lng = position.coords.longitude;
    });
    <?=isset($tweet_id)?"*/":""?>
    var watchID = null;
    var isWatch = false;
    var lat, lng;
    function getGeo(watchID) {
      if(lat != null && lng != null)
        map_move(lat, lng);

      watchID = navigator.geolocation.watchPosition(function(position) {
        if(isWatch) {
          map_move(position.coords.latitude, position.coords.longitude);
          lat = position.coords.latitude;
          lng = position.coords.longitude;
        }
      });
    }

    function map_move(lat, long) {
      var moveLatLon = new daum.maps.LatLng(lat, long);
      map.panTo(moveLatLon);
    }

    // 초기 로딩
    marker_load();

    daum.maps.event.addListener(map, 'dragstart', function() {
      watchID = null;
      isWatch = false;
    });
    daum.maps.event.addListener(map, 'dragend', function() {
      marker_load();
    });
    daum.maps.event.addListener(map, 'zoom_changed', function() {
      marker_load();
    });

    function marker_load() {
      /*if(map.getLevel() > 6)
        return;*/

      // 지도의 현재 영역을 얻어옵니다
      var bounds = map.getBounds();
      // 영역의 남서쪽 좌표를 얻어옵니다
      var swLatLng = bounds.getSouthWest();
      // 영역의 북동쪽 좌표를 얻어옵니다
      var neLatLng = bounds.getNorthEast();

      $.get("load-my-map.php",{swLat:swLatLng.getLat(),swLng:swLatLng.getLng(),neLat:neLatLng.getLat(),neLng:neLatLng.getLng()},function(data){
        for (var i = 0; i < data.length; i ++) {
          // 마커를 생성합니다
          var marker = new daum.maps.Marker({
              map: map, // 마커를 표시할 지도
              position: new daum.maps.LatLng(data[i].lat, data[i].lng) // 마커의 위치
          });

          // 마커에 표시할 인포윈도우를 생성합니다
          var infowindow = new daum.maps.InfoWindow({
              content: data[i].name // 인포윈도우에 표시할 내용
          });

          // 마커에 이벤트를 등록하는 함수 만들고 즉시 호출하여 클로저를 만듭니다
          // 클로저를 만들어 주지 않으면 마지막 마커에만 이벤트가 등록됩니다
          (function(marker, infowindow, data) {
              // 마커에 mouseover 이벤트를 등록하고 마우스 오버 시 인포윈도우를 표시합니다
              daum.maps.event.addListener(marker, 'mouseover', function() {
                infowindow.open(map, marker);
              });

              // 마커에 mouseout 이벤트를 등록하고 마우스 아웃 시 인포윈도우를 닫습니다
              daum.maps.event.addListener(marker, 'mouseout', function() {
                infowindow.close();
              });

              // 마커에 click 이벤트를 등록합니다
              daum.maps.event.addListener(marker, 'click', function() {
                window.open('tweet.php?tweet_id='+data.tweet_id, '_blank');
              });
          })(marker, infowindow, data[i]);
          console.log(i);
        }
      },"json");
    }
	</script>
</body>
</html>
