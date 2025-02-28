<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: sizex.inc.php,v 1.00 2005/11/25 21:24:06 takayan Exp $
// source:
// $Id: size.inc.php,v 1.10 2005/06/16 15:04:08 henoheno Exp $
//
// Text-size changing via CSS plugin - using "span.sizex"(x=1-7) class

define('PLUGIN_SIZEX_MAX', 7); // xx-large(size7)
define('PLUGIN_SIZEX_MIN', 1); // xx-small(size1)

// ----
define('PLUGIN_SIZEX_USAGE', '&sizex(1-7){Text you want to change};');

function plugin_sizex_inline()
{
	if (func_num_args() != 2) return PLUGIN_SIZEX_USAGE;

	list($size, $body) = func_get_args();

	// strip_autolink() is not needed for size plugin
	//$body = strip_htmltag($body);
	
	if ($size == '' || $body == '' || ! preg_match('/^\d+$/', $size))
		return PLUGIN_SIZEX_USAGE;

	$size = max(PLUGIN_SIZEX_MIN, min(PLUGIN_SIZEX_MAX, $size));
	return '<span class="size' . $size . '">' .
		$body . '</span>';
}
?>
