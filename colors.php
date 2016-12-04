<?php

require_once 'twitter.php';
require_once 'database.php';
$db = new Database('colors.db', 'setup.php');

error_reporting(E_ALL);
ini_set('error_log', __DIR__ . '/logs/error.log');

function choose_color() {
  $seed = rand(0, 16777216);
  $hex = dechex($seed);
  $hex = str_pad($hex, 6, '0', STR_PAD_LEFT);
  return strtoupper($hex);
}

function color_exists($hex) {
  global $db;
  $exists = $db->get_value("
    SELECT COUNT(hex)
    FROM color
    WHERE hex = ?
  ", array($hex));
  return (!empty($exists));
}

function tweet_color($hex) {
  include __DIR__ . '/config.php';  
  $twitter = new Twitter($app, $token);
  $tweet = $twitter->post('statuses/update_with_media.json', array(
    'status' => "#$hex",
    'media[]' => __DIR__ . "/$hex.png"
  ));
  //print_r($tweet);
  //print_r($twitter);
  return empty($tweet->errors);
}

function generate_image($hex) {
  $im = imagecreatetruecolor(527, 262);
  list($r, $g, $b) = sscanf($hex, "%02x%02x%02x");
  if (($r + $g + $b) > 382) {
    $tr = max(0, $r - 50);
    $tg = max(0, $g - 50);
    $tb = max(0, $b - 50);
  } else {
    $tr = min(255, $r + 50);
    $tg = min(255, $g + 50);
    $tb = min(255, $b + 50);
  }
  $bg_color = imagecolorallocate($im, $r, $g, $b);
  $text_color = imagecolorallocate($im, $tr, $tg, $tb);
  imagefill ($im, 0, 0, $bg_color);
  imagettftext($im, 24, 0, 186, 142, $text_color, __DIR__ . '/vera_sans/Vera.ttf', strtoupper("#$hex"));
  //imageline($im, 263, 0, 263, 261, $text_color);
  //imageline($im, 0, 131, 526, 131, $text_color);
  imagepng($im, "$hex.png");
  imagedestroy($im);
  return "$hex.png";
}

function save_color($hex) {
  global $db;
  $now = date('Y-m-d H:i:s');
  $db->query("
    INSERT INTO color
    (hex, created)
    VALUES (?, ?)
  ", array($hex, $now));
}

$hex = choose_color();
while (color_exists($hex)) {
  $hex = choose_color();
}
$filename = generate_image($hex);
if (!file_exists($filename)) {
  die("Could not generate image $filename.");
}

if (tweet_color($hex)) {
  save_color($hex);
}

if (file_exists("$hex.png")) {
  unlink("$hex.png");
}

?>
