<?php
/**
 * What's New プラグイン
 *
 * 特定階層以下に作成されたページを What's New として扱う。
 * 更新一覧と異なるのは、
 *  1. ページ名が表示され、リンク先はページの内容一行目になる
 *  2. 特定ディレクトリ以下の一覧なので、更新を強調したいページをリストできる
 * といったところ。
 *
 * @package org.pukiwiki.plugin.splitbody
 * @access	public
 * @author tetsuo morikawa momonga@users.sourceforge.jp
 * @create	2003/04/30
 * @version $Id: whatsnew.inc.php,v 1.7 2003/04/30 17:47:12 morikawa Exp $
 * 
 * !!!重要!!!
 * 04/03/12版以前のバージョンにはXSS脆弱性が含まれています。
 * 以前のバージョンをお使いの方は、新しいものにアップデートしてください。
 **/



/** ********** 初期設定 ********** **/
// デフォルトの表示件数
define('PLUGIN_WHATSNEW_RECENT_LINENUM', 10);
// デフォルトのリスト用表示
define('PLUGIN_WHATSNEW_LIST_MARK', '-'); // mark date - page_name と表示される



/**
 * inline 呼び出し
 *
 * inline 呼び出しで利用される
 *
 * @access	public
 * @param	String	$recent_line	表示する件数
 * @param	String	$prefix			ディレクトリ名(末尾の/なし)
 * @param	String	$list_mark		リスト表示用のマーク
 * @return	String	HTML結果
 */
function plugin_whatsnew_inline()
{
	global $script,$BracketName,$date_format;
	global $vars; // morikawa

	// default
	$prefix = strip_bracket($vars['page']).'/';
	$recent_lines = PLUGIN_WHATSNEW_RECENT_LINENUM; // 初期値。define する必要がある。
	$list_mark = PLUGIN_WHATSNEW_LIST_MARK;

	// set args
	if (func_num_args()>0 && func_num_args()<=3) {
		$args = func_get_args();
		if (is_numeric($args[0])) {
			$recent_lines = $args[0];
		}
		if (!empty($args[1])) {
			if (strlen($args[1])>0) {$prefix = $args[1].'/';}
		}
		if (!empty($args[2])) {
			if (strlen($args[2])>0) {$list_mark = array_pop($args[2]);}
		}
	} // if

	// get pages.
	$date  = '';
	$page = '';
	$link = '';
	$pages = array();
	foreach (get_existpages() as $page) {
		if (strpos($page,$prefix) === 0) {
    		$line = get_source($page);
    		list($date,$link) = explode(',',$line[0],2);
    		if (strlen(trim($link))<1){$link = $page;} // ページ指定ないとき自分自身
			$pages[] = trim($date).','.rawurlencode(trim($link)).','.str_replace($prefix,'',$page);
		}
	}
	// sort, get count.
	natcasesort($pages);
	$pages = array_reverse($pages, true); // ここ、一発でできるいい方法ないかなあ。
	$max = sizeof($pages);
	
	// loop begin
	$cnt_line = 0;
	$page = '';
	foreach ($pages as $data) {
		// get data.
		list($date,$link,$news) = explode(',',$data,3);
		
		// html strings.
		$items .= $list_mark.' '.$date;
		$items .= ' - <a href="'.$script.'?'.$link.'" ';
		$items .= 'title="'.rawurldecode($link).'">';
		$items .= $news;
		$items .= '</a><br />'."\n";
		// line counter
		$cnt_line++;
		if ($cnt_line >= $recent_lines) {break;}
	} // for
	return $items;
} //

/** ********************
ChangeLog:

2004-03-12 : XSS対応(引数の渡り方による差、引数の数の上限を見るよう修正)
2003-08-28 : 反射的に数字を直してしまったが、for抜ける位置なおさなきゃ駄目じゃん。
2003-08-28 : カウンタがひとつ多かったので修正。今まで気づかなかったぞい。
2003-05-01 : ページ指定ないとき自分自身をリンクするように修正
2003-05-01 : sort 逆順になるよう修正
2003-05-01 : title 属性の値がエンコードされているのを修正
2003-04-30 : 先頭が : で始まる :WhatsNew のようなページの場合の不具合解消

******************** **/
?>