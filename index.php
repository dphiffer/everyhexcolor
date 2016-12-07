<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>255 popular hex colors</title>
		<link rel="stylesheet" href="vera_sans/web/stylesheet.css">
		<link rel="stylesheet" href="everyhexcolor.css">
	</head>
	<body>
		<div id="colors">
			<?php
			
			require_once 'database.php';
			require_once 'rgbtohsv.php';
			$db = new Database('colors.db', 'setup.php');
			
			$colors = $db->query("
				SELECT *
				FROM color
				WHERE tweet_id NOT NULL
				ORDER BY interactions DESC
				LIMIT 255
			");

			$data = array();

			foreach ($colors as $color) {
				$hex = $color->hex;
				list($r, $g, $b) = sscanf($hex, "%02x%02x%02x");
				if (($r + $g + $b) > 382) {
					$shade = 'light';
			    $tr = max(0, $r - 50);
			    $tg = max(0, $g - 50);
			    $tb = max(0, $b - 50);
			  } else {
					$shade = 'dark';
			    $tr = min(255, $r + 50);
			    $tg = min(255, $g + 50);
			    $tb = min(255, $b + 50);
			  }
				$hsv = RGBtoHSV($r, $g, $b);
				$k1 = round($hsv[0] / 32);
				$k2 = round(2 * $hsv[1] + $hsv[2]);
				if (empty($data[$k1])) {
					$data[$k1] = array();
				}
				if (empty($data[$k1][$k2])) {
					$data[$k1][$k2] = array();
				}
				$size = ($color->interactions > 4) ? 'large' : 'small';
				$data[$k1][$k2][] = "<a href=\"https://twitter.com/everyhexcolor/status/$color->tweet_id\" class=\"$size $shade\" style=\"background-color: #$color->hex; color: rgb($tr, $tg, $tb); outline-color: rgb($tr, $tg, $tb);\" data-hex=\"#$color->hex\">#$color->hex</a>\n";
			}
		
			krsort($data, SORT_NUMERIC);
			foreach ($data as $v1) {
				ksort($v1, SORT_NUMERIC);
				foreach ($v1 as $v2) {
					foreach ($v2 as $link) {
						echo $link;
					}
				}
			}

		?>
		<br class="clear">
	</div>
	<script src="masonry.pkgd.min.js"></script>
	<script>
	
	new Masonry(document.getElementById('colors'), {
		itemSelector: 'a'
	});
	
	</script>
</body>
</html>
