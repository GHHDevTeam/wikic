<?php
/**
* @file
* @brief URLからタイトルを取得してリンクに置換するpukiwikiプラグイン
* @auther DEX http://dex.qp.land.to/
* @version v1.0 build $Revision: 320 $
* @date $Date: 2013-02-06 17:23:23 +0900 (水, 06  2月 2013) $
* @note
* - Licence: GNU GPLv3 http://sourceforge.jp/projects/opensource/wiki/GPLv3_Info
* - Install
*   - url.inc.php を pukiwiki/plugin/ にコピー
* @code
#url(http://www.example.jp/)  -> [[タイトル:http://www.example.jp/]]
&url(http://www.example.jp/); -> [[タイトル:http://www.example.jp/]]
* @endcode
*/

/// このプラグインの文字コード
defined('PLUGIN_URL_ENCODING') or define('PLUGIN_URL_ENCODING', 'UTF-8');

/// リンクタイトルの最大長
defined('PLUGIN_URL_TITLE_MAX') or define('PLUGIN_URL_TITLE_MAX', 255);

/// 初期化
function plugin_url_init()
{
	mb_substitute_character("none"); // エンコード変換できない外字を表示しない
	// $_<プラグイン名>_messages['msg_title'] でアクセス
	$messages = array(
		'_url_messages' => array(
			'title_collided' => '$1 で【更新の衝突】が起きました',
			'msg_collided'  => 'あなたがこのページを編集している間に、他の人が同じページを更新してしまったようです。',
		),
	);
	set_plugin_messages($messages);
}

/// オプション
function plugin_url_get_options($args = array())
{
	static $options = null;
	if( is_null($options) ){
		// 初期オプション
		$options = array(
		);
	}

	if(!$args) return $options;

	$argoptions = array();
	foreach ($args as $arg) { // key=val 分離だけ
		list($key, $val) = array_pad(explode('=', $arg, 2), 2, true);
		$argoptions[$key] = $val;
	}
	foreach ($argoptions as $key => $val) { // 型チェック
		if (array_key_exists($key, $options)) {
			$options[$key] = $val;
		}
	}

	return $options;
}

/// ブロック型 ( #プラグイン名() )
function plugin_url_convert()
{
	$args = func_get_args();
	
	return call_user_func_array('plugin_url_inline', $args);
}

/// インライン型 ( &プラグイン名(){}; )
function plugin_url_inline()
{
	global $script,$vars,$digest;
	global $_url_messages;
	$result = '';
	$args = func_get_args();
	$options = plugin_url_get_options($args);
	list($url) = $args;

	$url_utf8 = $url;
	$encoding = mb_detect_encoding($url);
	if(strtolower($encoding) != 'utf-8') {
		$url_utf8 = mb_convert_encoding($url, 'utf-8', $encoding);
	}
	$url_utf8 = plugin_url_normalize($url_utf8); // 日本語を含むURLを正規化

	$title = null;
	// *.htm, *.htmlだけで良いが、「末尾が/」に対応できないため全て取得してから確認
	if( preg_match('/^https?:\/\/.+$/i', $url_utf8, $matches) ){
		$url_utf8 = $matches[0];
		$title = plugin_url_get_title($url_utf8);
		$result = sprintf('<a href="%s">%s</a>'
			, $url_utf8
			, htmlspecialchars($title ? $title : $url_utf8)
			);
	}else{
		$result = htmlspecialchars($url);
	}

	// ページの更新時にプラグインをリンクに置き換える
	$update = true;
	if( isset($vars['preview']) || isset($vars['realview'])) $update = false;
	if($update) plugin_url_replace_link($url, $url_utf8, $title);

	return $result;
}

/// urlからコンテンツを取得
function plugin_url_get_content($url)
{
	$urldata = false;
	$user_agent='Mozilla/5.0 (Windows; U; Windows NT 5.1; ja; rv:1.9.2) Gecko/20100115 Firefox/3.6 (.NET CLR 3.5.30729)';

	if( extension_loaded('curl') ) {
		 $ch = curl_init();
		 curl_setopt($ch, CURLOPT_URL, $url);
		 curl_setopt($ch, CURLOPT_HEADER, 0);
		 curl_setopt($ch, CURLOPT_ENCODING, "gzip");
		 curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
		 ob_start();
		 curl_exec ($ch);
		 curl_close ($ch);
		 $urldata = ob_get_contents();
		 ob_end_clean();
	}else if ( ini_get("allow_url_fopen") ) {
		// enable allow_url_fopen
		if( version_compare(PHP_VERSION, '5.0', '>=') ){
			$option = array(
				'http' => array(
					'timeout' => 5,
					'method' => 'GET',
					'header' => 'Referer: ' . $url . "\r\n"
								. 'User-Agent: ' . $user_agent . "\r\n"
								. 'Connection: close' . "\r\n"
				)
			);
			$context = stream_context_create($option);
			$urldata = @file_get_contents($url, 0, $context);
		}else{
			$urldata = @file_get_contents($url);
		}
	}

	return $urldata;
}

/// urlからタイトル取得
function plugin_url_get_title($url){
	$title = false;
	$urldata = plugin_url_get_content($url);
	
	if($urldata === false) return $title; // 取得失敗
	if( preg_match('#[^\'\"]<title[^\>]*>(.*?)</title>[^\'\"]#is', $urldata, $matches) ){
		$encoding = mb_detect_encoding($urldata);

		$title = $matches[1];
		if(preg_match("/&#[xX]*[0-9a-zA-Z]{2,8};/", $title)){ // 数値参照形式 -> 文字列
			$title = plugin_url_nument2chr($title, $encoding);
		}

		$title = mb_convert_encoding($title, PLUGIN_URL_ENCODING, $encoding);// 内部文字コードに変換
		$title = html_entity_decode($title, ENT_QUOTES, PLUGIN_URL_ENCODING);
		$title = plugin_url_mb_trim($title);
		$title = mb_strimwidth($title, 0, PLUGIN_URL_TITLE_MAX, "...", PLUGIN_URL_ENCODING); // 長すぎる場合はカット
		$title = mb_convert_encoding($title, SOURCE_ENCODING, PLUGIN_URL_ENCODING); // 出力文字コードに変換
	}

	return $title;
}

/// 数値文字参照を文字に変換(&#123456; &#x0000;)
function plugin_url_nument2chr($string, $encode_to='UTF-8') {
	// 文字コードチェック、mb_detect_order()が関係する
	$encoding = strtolower(mb_detect_encoding($string));
	if (!preg_match("/^utf/", $encoding) and $encoding != 'ascii') {
		return '';
	}
	
	// 16 進数の文字参照(らしき表記)が含まれているか
	$excluded_hex = $string;
	if (preg_match("/&#[xX][0-9a-zA-Z]{2,8};/", $string)) {
		// 16 進数表現は 10 進数に変換
		$excluded_hex = preg_replace("/&#[xX]([0-9a-zA-Z]{2,8});/e", "'&#'.hexdec('$1').';'", $string);
	}
	return mb_decode_numericentity($excluded_hex, array(0x0, 0x10000, 0, 0xfffff), $encode_to);
}

/// リンク置換
function plugin_url_replace_link($url, $url_utf8, $title)
{
	global $script, $vars, $now;
	global $_title_collided, $_msg_collided, $_title_updated;
	global $_url_messages;

	$refer = isset($vars['refer']) ? $vars['refer'] : '';
	$page  = isset($vars['page'])  ? $vars['page']  : '';
	$page  = get_fullname($page, $refer);

	if (! is_pagename($page))
		return array(
			'msg' =>'Invalid page name',
			'body'=>'' ,
			'collided'=>TRUE
		);

//	check_editable($page, true, true);

	$ret = array('msg' => $_title_updated, 'collided' => FALSE);

	$link = '';
	if($title){
		$link = '[[' . htmlspecialchars(strip_bracket($title)) . ':' . $url_utf8 . ']]';
	}else{
		$link = htmlspecialchars($url_utf8);
	}

	$postdata = null;
	if (! is_page($page)) {
		$postdata = $link;
	} else {
		$postdata_old = get_source($page);
		$count    = count($postdata_old);

		$digest = isset($vars['digest']) ? $vars['digest'] : '';
		if (md5(join('', $postdata_old)) != $digest) {
			$ret['msg']  = plugin_url_convert_encoding($_url_messages['title_collided']);
			$ret['body'] = plugin_url_convert_encoding($_url_messages['msg_collided']);
		}

		foreach($postdata_old as $i => $line){
			if(preg_match('#^[\s\t]*//#', $line)){
				// コメント行
			}else{
				$search  = array(
					'&url('.$url.');',
					'#url('.$url.')',
				);
				$replace = array(
					$link,
					$link,
				);
				$line = str_replace($search, $replace, $line);
			}

			$postdata .= $line;
		}
	}
	page_write($page, $postdata);

	return $ret;
}

function plugin_url_convert_encoding($str)
{
	if(PLUGIN_URL_ENCODING == SOURCE_ENCODING) return $str;
	
	return mb_convert_encoding($str, SOURCE_ENCODING, PLUGIN_URL_ENCODING);
}

/// マルチバイトtrim
function plugin_url_mb_trim($str)
{
	$whitespace = '[\s\0\x0b\p{Zs}\p{Zl}\p{Zp}]';
	$pattern = array(
		sprintf('/(^%s+|%s+$)/u', $whitespace, $whitespace), // 前後の空白
		"/[\r\n]+/", // 改行
		"/[\s]+/", // 空白の連続
	);
	$replacement = array(
		'',
		'',
		' ',
	);
	$ret = preg_replace($pattern, $replacement, $str);
	return $ret;
}

/**
* URL正規化。日本語が含まれたURLをurlencodeして返す
*
* @param	string	$url	URL
* @return	string			正規化したURL
*/
function plugin_url_normalize($url, $options=array()) {
	$tmp = parse_url($url);
	$paths = explode("/",$tmp["path"]);
	
	$pattern = "/^[-_.!~*'()a-zA-Z0-9;\/\?:@&=+$,%#]+$/";
	foreach($paths as $key => $val){
		if( !preg_match($pattern, $val, $matches) ){
			$paths[$key] = urlencode($val);
		}
	}

	return sprintf("%s://%s%s%s%s%s",
		$tmp['scheme']
		, isset($tmp['user']) ? $tmp['user'] . ':' . $tmp['pass'] . '@' : ''
		, $tmp['host']
		, implode("/", $paths)
		, isset($tmp['query']) ? '?' . $tmp['query'] : ''
		, isset($tmp['fragment']) ? '#' . $tmp['fragment'] : ''
	);
}
