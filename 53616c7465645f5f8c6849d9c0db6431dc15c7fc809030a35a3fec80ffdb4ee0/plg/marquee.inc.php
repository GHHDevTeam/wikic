<?php
//////////////////////////////////////////////////////////////////////
// marquee.inc.php
//       by teanan / Interfair Laboratory 2004.
// marqueeプラグイン

/*
**使い方
 #marquee(文字列,[behavior],[loop],[背景色]);

**引数
-文字列 (",)"等は使えませんのでご注意)
-behavior
--scroll(省略時) スクロール表示
--slide 端にぶつかると停止
--alternate 端から端までを往復
-loop ループする回数
-背景色

*/

// [更新履歴]
// 2004-08-03 version 1.0

//
// (tab=4)

function plugin_marquee_convert()
{
	global $vars;

	list($body,$behavior,$loop,$bgcolor) = func_get_args();
	$body = htmlspecialchars($body);

	$retval = '<marquee';
	if($bgcolor!='')
	{
		$bgcolor = htmlspecialchars($bgcolor);
		$retval .= " bgcolor=$bgcolor";
	}
	if($loop!='')
	{
		$loop = htmlspecialchars($loop);
		$retval .= " loop=$loop";
	}
	if($behavior!='')
	{
		$behavior = htmlspecialchars($behavior);
		$retval .= " behavior=$behavior";
	}
	$retval .= ">$body</marquee>";

	return $retval;
}
?>