<?php
// Plugin for PukiWiki
// $Id: alias.inc.php,v 0.1 2017/03/04 HarukaTomose
// alias プラグインがなくなっていたので、独自に作成。
// ついでに、alias 方式の弱点を解消するため、
// alias 実行されたものについて記録を残すようにした。

define('ALIAS_DATA_DIR', DATA_HOME . 'alias/'); 

function plugin_alias_convert()
{
	global $vars;
	$args = func_get_args();
	$args = explode('#',$args[0]);
	// エラー処理系
	// 自分自身への転送は禁止。
	if($vars['page']==$args[0]) return '#alias: Can not set Selfpage.';
	//「ページ」でない指定も禁止。
	if(! is_page($args[0])) return '#alias: Bad parameter.';
	// すでに alias で指定されているページへの alias 禁止。
	// 多重alias による負荷や、最悪「ループする関係」の構築を避けるための処理。
	$filename = ALIAS_DATA_DIR.encode($args[0]).".txt";
	if( file_exists($filename)) return '#alias: Can not alias to already aliased page.';

	// ここまで来たら、alias 許可。現在の 'page' の表示要求は arg[0]に跳ばす。
	$jumpto = urlencode($args[0]);
	$aname = urlencode($args[1]);
	
	$filename = ALIAS_DATA_DIR.encode($vars['page']).".txt";

	// alias の記録。記録を残すことで、後で拾いやすくする。
	// 現在の'page'をファイル名に、その中にalias先を記録。
	// ・・・まあ、中身を利用する予定は今のところない（笑）
	if( !file_exists($filename)){
		$writedata = $args[0];
		$fp = fopen($filename, 'w');
		flock($fp, LOCK_EX);
		fwrite($fp, $writedata);
		flock($fp, LOCK_UN);
		fclose($fp);
	}

	// alias のジャンプ処理実体
	if($aname) header('Location: ' . get_base_uri() .'?'. $jumpto .'#'. $aname);
	else  header('Location: ' . get_base_uri() .'?'. $jumpto);
	
	exit;

}
