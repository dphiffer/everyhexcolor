<?php

require_once 'twitter.php';
require_once 'database.php';
require_once 'config.php';

$db = new Database('colors.db', 'setup.php');
$twitter = new Twitter($app, $token);

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
  global $twitter;
  $tweet = $twitter->post('statuses/update_with_media.json', array(
    'status' => "#$hex",
    'media[]' => __DIR__ . "/$hex.png"
  ));
  //print_r($tweet);
  //print_r($twitter);
  return $tweet;
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

function save_color($hex, $tweet_id) {
  global $db;
  $now = gmdate('Y-m-d H:i:s');
  $db->query("
    INSERT INTO color
    (hex, tweet_id, created)
    VALUES (?, ?, ?)
  ", array($hex, $tweet_id, $now));
}

function gather_tweet_stats() {
  global $db, $twitter;
  $tweet_id = $db->get_value("
    SELECT tweet_id
    FROM color
    WHERE tweet_id NOT NULL
    ORDER BY tweet_id
    LIMIT 1
  ");
  $rsp = $twitter->get('statuses/user_timeline.json', array(
    'screen_name' => 'everyhexcolor',
    'max_id' => ($tweet_id - 1),
    'count' => 200,
    'trim_user' => 1
  ));
  foreach ($rsp as $tweet) {
    if (!preg_match('/^#(\w{6})/', $tweet->text, $matches)) {
      continue;
    }
    $tweet_id = $tweet->id_str;
    $faves = $tweet->favorite_count;
    $retweets = $tweet->retweet_count;
    $interactions = $faves + $retweets;
    $hex = $matches[1];
    $db->query("
      UPDATE color
      SET tweet_id = ?,
          faves = ?,
          retweets = ?,
          interactions = ?
      WHERE hex = ?
    ", array($tweet_id, $faves, $retweets, $interactions, $hex));
  }
}

$hex = choose_color();
while (color_exists($hex)) {
  $hex = choose_color();
}
$filename = generate_image($hex);
if (!file_exists($filename)) {
  die("Could not generate image $filename.");
}

$tweet = tweet_color($hex);
if (empty($tweet->errors)) {
  save_color($hex, $tweet->id);
}

if (file_exists("$hex.png")) {
  unlink("$hex.png");
}

gather_tweet_stats();

?>
