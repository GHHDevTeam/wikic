<?php
//////////////////////////////////////////////////////////////////////
// rothtml.inc.php
//       by teanan / Interfair Laboratory 2004.
// HTMLファイルを順に一つずつincludeするプラグイン

// [更新履歴]
// 2004-10-16 version 1.0 [初版]
// 2004-10-26 version 1.1
// ・取り込むHTMLファイル名を正規表現で指定できるように機能追加。
// ・ディレクトリにファイルが無い場合の不具合を修正。
// 2005-03-22 version 1.2
// ・正規表現をもう少し厳密に(最後に$を付けただけ)。
// ・第２引数'random'追加。

//ファイルのディレクトリ
define('PLUGIN_ROTHTML_INCDIR'  , DATA_HOME  .'rothtml/');
define('PLUGIN_ROTHTML_COUNTER' , COUNTER_DIR.'rothtml.count');
define('PLUGIN_ROTHTML_FILEMASK', '[0-9A-Z]+\.inc');

function plugin_rothtml_inline()
{
	$dir  = PLUGIN_ROTHTML_INCDIR;

	list($mask, $random) = func_get_args();
	if($mask == '')
	{
		$mask = PLUGIN_ROTHTML_FILEMASK;
	}

	//  指定されたパスのファイルのリストを取得する
	unset($files);
	$dp = @opendir($dir) or
		die_message($dir . ' is not found or not readable.');
	while ($filename = readdir($dp))
	{
		if(preg_match("/$mask$/i",$filename))
		{
			$files[] = $dir.$filename;
		}
	}
	closedir($dp);

	if(count($files)==0)
	{
		return '';		// 該当なし
	}

	// 表示するファイル番号を決定する
	$filenum = count($files);
	if($random == 'random')
	{
		// ランダムシードの設定
		$counter = mt_rand(0, ($filenum - 1));
	}
	else
	{
		// カウンタファイルが存在する場合は読み込む
		$cfile = PLUGIN_ROTHTML_COUNTER;
		$fp = fopen($cfile, file_exists($cfile) ? 'r+' : 'w+')
			or die_message('rothtml.inc.php:cannot open '.$cfile);

		$counter = (trim(fgets($fp,256)) + 1) % $filenum;

		set_file_buffer($fp, 0);
		flock($fp,LOCK_EX);
		rewind($fp);
		ftruncate($fp,0);

		fputs($fp,"$counter\n");
		// ファイルを閉じる
		flock($fp,LOCK_UN);
		fclose($fp);
	}

	$filename = $files[$counter];
	$lines = file($filename)
		or die_message(htmlspecialchars($filename). ' is not found or not readable.');
	$body = join('',$lines);

	if(function_exists('mb_convert_encoding'))
	{
		// ファイル名の文字コードを変換
		$body = mb_convert_encoding($body, SOURCE_ENCODING, "auto");
	}
	return $body;
}
?>