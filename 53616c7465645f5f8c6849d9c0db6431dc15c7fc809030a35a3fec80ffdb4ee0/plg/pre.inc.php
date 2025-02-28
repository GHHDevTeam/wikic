<?php
/**
 * a part of code.inc.php
 * Time-stamp: <05/08/03 21:27:10 sasaki>
 * 
 * GPL
 */

require_once(PLUGIN_DIR . 'code.inc.php');

function plugin_pre_init()
{
	if (function_exists('plugin_code_init'))
		plugin_code_init();
}
?>
