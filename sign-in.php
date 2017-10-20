<?php include_once("analyticstracking.php") ?>
<?php
session_start();
require 'config.php';

require 'twitteroauth/autoload.php';
use Abraham\TwitterOAuth\TwitterOAuth;

$callback = (isset($_GET['url'])?$_GET['url']:'my-map.php');

if(isset($_SESSION['access_token'])) {
  header("Location: {$callback}");
} else {
  $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);

  $request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => "https://gabolga.gamjaa.com/callback.php?url={$callback}"));

  $_SESSION['oauth_token'] = $request_token['oauth_token'];
  $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

  //echo $_SESSION['oauth_token'].'<br>'.$_SESSION['oauth_token_secret'];

  $url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));

  //echo $url;
  header("Location: {$url}");
}
?>
