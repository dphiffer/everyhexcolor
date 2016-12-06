<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>36 popular colors</title>
		<link rel="stylesheet" href="vera_sans/web/stylesheet.css">
		<style>

		body {
			font-family: bitstream_vera_sansroman, menlo, monospace;
			margin: 0;
		}
		
		a {
			font-size: 1.2em;
			width: calc((100vw - 15px) / 5);
			height: 12vw;
			float: left;
			text-align: center;
			text-decoration: none;
			line-height: 12vw;
		}
		
		.clear {
			clear: both;
		}
		
		</style>
	</head>
	<body>
		<?php
		
		require_once 'database.php';
		$db = new Database('colors.db', 'setup.php');
		
		$colors = $db->query("
			SELECT *
			FROM color
			WHERE tweet_id NOT NULL
			ORDER BY interactions DESC
			LIMIT 300
		");
		

	$data = array();

	foreach ($colors as $color) {
		$hex = $color->hex;
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
		$hsv = RGBtoHSV($r, $g, $b);
		$val = round($hsv[2] / 75);
		$hue = round($hsv[0]);
		if (empty($data[$val])) {
			$data[$val] = array();
		}
		if (empty($data[$val][$hue])) {
			$data[$val][$hue] = array();
		}
		$data[$val][$hue][] = "<a href=\"https://twitter.com/everyhexcolor/status/$color->tweet_id\" style=\"background-color: #$color->hex; color: rgb($tr, $tg, $tb);\" title=\"Hue: $hsv[0]\">#$color->hex</a>\n";
	}
	
	krsort($data, SORT_NUMERIC);
	foreach ($data as $hue) {
		ksort($hue, SORT_NUMERIC);
		foreach ($hue as $links) {
			foreach ($links as $link) {
				echo $link;
			}
		}
	}

	// http://stackoverflow.com/a/13887939/937170

	/**
	 * Licensed under the terms of the BSD License.
	 * (Basically, this means you can do whatever you like with it,
	 *   but if you just copy and paste my code into your app, you
	 *   should give me a shout-out/credit :)
	 */
	
	function RGBtoHSV($R, $G, $B)    // RGB values:    0-255, 0-255, 0-255
{                                // HSV values:    0-360, 0-100, 0-100
    // Convert the RGB byte-values to percentages
    $R = ($R / 255);
    $G = ($G / 255);
    $B = ($B / 255);

    // Calculate a few basic values, the maximum value of R,G,B, the
    //   minimum value, and the difference of the two (chroma).
    $maxRGB = max($R, $G, $B);
    $minRGB = min($R, $G, $B);
    $chroma = $maxRGB - $minRGB;

    // Value (also called Brightness) is the easiest component to calculate,
    //   and is simply the highest value among the R,G,B components.
    // We multiply by 100 to turn the decimal into a readable percent value.
    $computedV = 100 * $maxRGB;

    // Special case if hueless (equal parts RGB make black, white, or grays)
    // Note that Hue is technically undefined when chroma is zero, as
    //   attempting to calculate it would cause division by zero (see
    //   below), so most applications simply substitute a Hue of zero.
    // Saturation will always be zero in this case, see below for details.
    if ($chroma == 0)
        return array(0, 0, $computedV);

    // Saturation is also simple to compute, and is simply the chroma
    //   over the Value (or Brightness)
    // Again, multiplied by 100 to get a percentage.
    $computedS = 100 * ($chroma / $maxRGB);

    // Calculate Hue component
    // Hue is calculated on the "chromacity plane", which is represented
    //   as a 2D hexagon, divided into six 60-degree sectors. We calculate
    //   the bisecting angle as a value 0 <= x < 6, that represents which
    //   portion of which sector the line falls on.
    if ($R == $minRGB)
        $h = 3 - (($G - $B) / $chroma);
    elseif ($B == $minRGB)
        $h = 1 - (($R - $G) / $chroma);
    else // $G == $minRGB
        $h = 5 - (($B - $R) / $chroma);

    // After we have the sector position, we multiply it by the size of
    //   each sector's arc (60 degrees) to obtain the angle in degrees.
    $computedH = 60 * $h;

    return array($computedH, $computedS, $computedV);
}

	?>
	<br class="clear">
</body>
</html>
