<?php /* vim: set ts=4 noexpandtab : */
/* Pukiwiki GoogleMaps plugin 3.4.0
 * http://reddog.s35.xrea.com
 * -------------------------------------------------------------------
 * Copyright (c) 2005-2017 OHTSUKA, Yoshio
 * This program is free to use, modify, extend at will. The author(s)
 * provides no warrantees, guarantees or any responsibility for usage.
 * Redistributions in any form must retain this copyright notice.
 * ohtsuka dot yoshio at gmail dot com
 * -------------------------------------------------------------------
 * 変更履歴はgooglemaps3.inc.php
 */

define ('PLUGIN_GOOGLEMAPS3_ICON_IMAGE', 'http://www.google.com/mapfiles/marker.png');
define ('PLUGIN_GOOGLEMAPS3_ICON_SHADOW','http://www.google.com/mapfiles/shadow50.png');
define ('PLUGIN_GOOGLEMAPS3_ICON_IW', 20);
define ('PLUGIN_GOOGLEMAPS3_ICON_IH', 34);
define ('PLUGIN_GOOGLEMAPS3_ICON_SW', 37);
define ('PLUGIN_GOOGLEMAPS3_ICON_SH', 34);
define ('PLUGIN_GOOGLEMAPS3_ICON_IANCHORX', 10);
define ('PLUGIN_GOOGLEMAPS3_ICON_IANCHORY', 34);
define ('PLUGIN_GOOGLEMAPS3_ICON_SANCHORX', 10);
define ('PLUGIN_GOOGLEMAPS3_ICON_SANCHORY', 0);
define ('PLUGIN_GOOGLEMAPS3_ICON_TRANSPARENT', 'http://www.google.com/mapfiles/markerTransparent.png');
define ('PLUGIN_GOOGLEMAPS3_ICON_AREA', '1 7 7 0 13 0 19 7 19 12 13 20 12 23 11 34 9 34 8 23 6 19 1 13 1 70');

function plugin_googlemaps3_icon_get_default () {
	return array(
		'image'       => PLUGIN_GOOGLEMAPS3_ICON_IMAGE,
		'shadow'      => PLUGIN_GOOGLEMAPS3_ICON_SHADOW,
		'iw'          => PLUGIN_GOOGLEMAPS3_ICON_IW,
		'ih'          => PLUGIN_GOOGLEMAPS3_ICON_IH,
		'sw'          => PLUGIN_GOOGLEMAPS3_ICON_SW,
		'sh'          => PLUGIN_GOOGLEMAPS3_ICON_SH,
		'ianchorx'    => PLUGIN_GOOGLEMAPS3_ICON_IANCHORX,
		'ianchory'    => PLUGIN_GOOGLEMAPS3_ICON_IANCHORY,
		'sanchorx'    => PLUGIN_GOOGLEMAPS3_ICON_SANCHORX,
		'sanchory'    => PLUGIN_GOOGLEMAPS3_ICON_SANCHORY,
		'transparent' => PLUGIN_GOOGLEMAPS3_ICON_TRANSPARENT,
		'area'        => PLUGIN_GOOGLEMAPS3_ICON_AREA
	);
}

function plugin_googlemaps3_icon_convert() {
	$args = func_get_args();
	return plugin_googlemaps3_icon_output($args[0], array_slice($args, 1));
}

function plugin_googlemaps3_icon_inline() {
	$args = func_get_args();
	array_pop($args);
	return plugin_googlemaps3_icon_output($args[0], array_slice($args, 1));
}

function plugin_googlemaps3_icon_output($name, $params) {
	global $vars;
	
	if (!defined('PLUGIN_GOOGLEMAPS3_DEF_MAPNAME')) {
		return "googlemaps3_icon: error googlemapsを先に呼び出してください。<br/>";
	}
	if (!plugin_googlemaps3_is_supported_profile()) {
		return '';
	}

	$defoptions = plugin_googlemaps3_icon_get_default();
	
	$inoptions = array();
	foreach ($params as $param) {
		$pos = strpos($param, '=');
		if ($pos == false) continue;
		$index = trim(substr($param, 0, $pos));
		$value = htmlspecialchars(trim(substr($param, $pos+1)));
		$inoptions[$index] = $value;
	}
	
	if (array_key_exists('define', $inoptions)) {
		$vars['googlemaps3_icon'][$inoptions['define']] = $inoptions;
		return "";
	}
	
	$coptions = array();
	if (array_key_exists('class', $inoptions)) {
		$class = $inoptions['class'];
		if (array_key_exists($class, $vars['googlemaps3_icon'])) {
			$coptions = $vars['googlemaps3_icon'][$class];
		}
	}
	$options = array_merge($defoptions, $coptions, $inoptions);
	$image       = $options['image'];
	$shadow      = $options['shadow'];
	$iw          = (integer)$options['iw'];
	$ih          = (integer)$options['ih'];
	$sw          = (integer)$options['sw'];
	$sh          = (integer)$options['sh'];
	$ianchorx    = (integer)$options['ianchorx'];
	$ianchory    = (integer)$options['ianchory'];
	$sanchorx    = (integer)$options['sanchorx'];
	$sanchory    = (integer)$options['sanchory'];
	$transparent = $options['transparent'];
	$area        = $options['area'];

	$coords = array();
	if (isset($area)) {
		$c = substr($area, 0, 1);
		switch ($c) {
			case "'":
			case "[";
			case "{";
				$area = substr($area, 1, strlen($area)-2);
				break;
			case "&":
				if (substr($area, 0, 6) == "&quot;") {
					$area = substr($area, 6, strlen($area)-12);
				}
				break;
		}
		foreach (explode(' ', $area) as $p) {
			if (strlen($p) <= 0) continue;
			array_push($coords, $p);
		}
	}
	$coords = join($coords, ",");
	$page = $vars['page'];

	// Output
	$output = <<<EOD
<script type="text/javascript">
//<![CDATA[
onloadfunc.push( function () {
	var icon = new google.maps.MarkerImage();
	icon.image = "$image";
	icon.shadow = "$shadow";
	icon.iconSize = new google.maps.Size($iw, $ih);
	icon.shadowSize = new google.maps.Size($sw, $sh);
	icon.iconAnchor = new google.maps.Point($ianchorx, $ianchory);
	icon.infoWindowAnchor = new google.maps.Point($sanchorx, $sanchory);
	icon.transparent = "$transparent";
	icon.imageMap = [$coords];
	icon.pukiwikiname = "$name";
	googlemaps_icons["$page"]["$name"] = icon;
});
//]]>
</script>

EOD;
	return $output;
}

?>
