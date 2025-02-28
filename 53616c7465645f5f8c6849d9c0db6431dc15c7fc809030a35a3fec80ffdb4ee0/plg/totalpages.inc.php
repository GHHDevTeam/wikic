<?php
/*
PukiWiki - Yet another WikiWikiWeb clone.
totalpages.inc.php, v1.00 2020 M.Taniguchi
License: GPL v3 or (at your option) any later version

ウィキ内のページ数を数えて出力するPukiWiki用プラグイン。

【使い方】
&totalpages;
&totalpages(対象ページ名接頭辞);

【引数】
対象ページ名接頭辞 … この文字列から始まる名前のページのみを数える

【使用例】
このウィキには&totalpages;本の記事があります。
花に関する記事は&totalpages(花/);本です。

【処理の詳細】
数えるのは、次のすべての条件で絞り込まれたページです。

1) RecentChanges ページを除外する
2) pukiwiki.ini.php 内 $non_list で指定されたページを除外する
3) PLUGIN_TOTALPAGES_PAGE_ALLOW 定数が空でなければ、それが表すページのみを対象とする
4) PLUGIN_TOTALPAGES_PAGE_DISALLOW 定数が空でなければ、それが表すページを除外する（デフォルトでは「Pukiwiki/」から始まるページを除外）
5) 引数が空でなければ、その文字列から始まるページのみを対象とする
6) 閲覧不可のページを除外する

数えたページ数は専用のキャッシュファイル cache/totalpages[.*].dat に保存し、次回から処理を省略します。
cache/recent.dat のタイムスタンプを参照し、ページの更新・増減を検知したら数え直します。
もしプラグイン内の定数やコードを書き換えて条件を変更したら、キャッシュファイル cache/totalpages[.*].dat を削除するか適当なページを更新して強制的に数え直させてください。

【謝辞】
次のプラグインを参考にし、コードを一部流用させていただきました。
sitemap.inc.php : Google-Sitemaps plugin - Create Google-Sitemaps. Copyright (C) JuJu License: GPL v2 or (at your option) any later version
*/

define('PLUGIN_TOTALPAGES_PAGE_ALLOW', '');	// カウント対象ページ名を表す正規表現
define('PLUGIN_TOTALPAGES_PAGE_DISALLOW', '^(Pukiwiki\/.*)$');	// カウント除外ページ名を表す正規表現

define('PLUGIN_TOTALPAGES_NUMBERFORMAT', true);	// カウント結果をカンマで区切るかどうか


function plugin_totalpages_convert() { return ''; }

function plugin_totalpages_inline() {
	global $whatsnew, $non_list;

	list($prefix) = func_get_args();

	$cachefile = CACHE_DIR . 'totalpages' . (($prefix != '')? '.' . encode($prefix) : '') . '.dat';	// カウント結果キャッシュファイルパス
	$recentfile = CACHE_DIR . 'recent.dat';	// ページ更新キャッシュファイルパス
	$num = 0;	// カウント数

	// カウント結果キャッシュファイルがない、またはページ更新キャッシュファイルより古かったらカウント実行
	if (!file_exists($cachefile) || (file_exists($recentfile) && (filemtime($cachefile) < filemtime($recentfile)))) {
		foreach(get_existpages() as $page) {
			if (($page != $whatsnew)	// RecentChangesページを除外
				&& !preg_match("/$non_list/", $page)	// $non_list に該当するページを除外
				&& ((PLUGIN_TOTALPAGES_PAGE_ALLOW     == '') ||  preg_match('/' . PLUGIN_TOTALPAGES_PAGE_ALLOW    . '/', $page))	// 定数指定があれば該当するページのみ対象
				&& ((PLUGIN_TOTALPAGES_PAGE_DISALLOW  == '') || !preg_match('/' . PLUGIN_TOTALPAGES_PAGE_DISALLOW . '/', $page))	// 定数指定があれば該当するページを除外
				&& (($prefix == '') || mb_strpos($page, $prefix) === 0)	// 引数による接頭辞指定があれば該当するページのみ対象
				&& check_readable($page, false, false)	// 閲覧可能なページのみ対象（ただし、これを実行するユーザーの権限において）
			) {
				$num++;	// カウント
			}
		}

		// カウント結果キャッシュファイル書き込み
		$fp = fopen($cachefile, 'w');
		flock($fp, LOCK_EX);
		rewind($fp);
		fputs($fp, $num);
		flock($fp, LOCK_UN);
		fclose($fp);
	} else {
		// カウント結果キャッシュファイル読み込み
		$fp = fopen($cachefile, 'r');
		$num = (int)fgets($fp);
		fclose($fp);
	}

	// 3桁カンマ区切り
	if (PLUGIN_TOTALPAGES_NUMBERFORMAT) $num = number_format($num);

	return $num;
}