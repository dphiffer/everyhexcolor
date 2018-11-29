<?php

$hex = $_GET['hex'];
if (! preg_match('/^[0-9a-f]{6}$/', $hex)) {
	$hex = '000000';
}

header('Content-Type: image/png');
generate_image($hex);

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
	imagepng($im);
}
