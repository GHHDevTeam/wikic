<?php
// 任意のページのファイル名を表示するプラグイン
// $Id: filename.inc.php,v 1.0 2005/04/12 03:34:17 ichise Exp $

function plugin_filename_inline()
{
	global $vars, $WikiName, $BracketName;

	$args = func_get_args();
	$page = $args[0];

	if ($page == ''){
		$page = $vars['page'];
	} else {
		if (preg_match("/^($WikiName|$BracketName)$/", strip_bracket($page))) {
			$page = get_fullname(strip_bracket($page), $vars['page']);
		} else {
			return FALSE;
		}
	}
	if (! is_page($page)) return FALSE;
	
	return encode($page) . '.txt';
}
?>
