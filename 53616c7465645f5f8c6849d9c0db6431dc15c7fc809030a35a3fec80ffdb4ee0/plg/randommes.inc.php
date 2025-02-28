<?php
//////////////////////////////////////////////////////////////////////
// randommes.inc.php
// ランダムメッセージプラグイン
//     written by XZR rev.6_1 (2004040301)
//////////////////////////////////////////////////////////////////////
// 別途用意したテキストファイル、もしくはページの1行をランダムに表示します。
// 引数：
// 第1引数：ファイル名、もしくはページ名（必須）
// 第2引数：表示モード
//          always: 毎度表示が変わります（デフォルト）
//          daily : 日替わり
// 第3引数：読み込みモード
//          file: ファイルから（デフォルト）
//          page: ページから
//////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////

// 読み込みページが認証対象ページの場合の動作
// 　　　読み込まない（デフォルト）：								'noread'
// 　　　構わず読み込む：											'read'
// 　　　ユーザの現アクセス権限でチェック：							'check1'
// 　　　ユーザのアクセス権限でチェック（未認証ならダイアログ）：	'check2'
define("PLUGIN_RANDOMMES_AUTH_MODE", 'noread');

// readfile path
define("PLUGIN_RANDOMMES_FILE_DIR", "./random/");

// プラグインの初期化
function plugin_randommes_init() {
	$_messages = plugin_randommes_retMsg(LANG);
	set_plugin_messages($_messages);
}

// メッセージの設定
function plugin_randommes_retMsg($lang) {
	if($lang == 'ja') {
		$msg = array(
			'_randommes_messages'=>array(
				'_msg_args_err' => 'randommes：引数が正しくありません',
				'_msg_file_err' => 'randommes：ファイルが存在しません',
				'_msg_page_err' => 'randommes：ページが存在しません',
				'_msg_auth_err1' => 'randommes：ページの参照権限がありません',
				'_msg_auth_err2' => 'randommes：現在、ページの参照権限がありません'
			)
		);
	}
	else {
		$msg = array(
			'_randommes_messages'=>array(
				'_msg_args_err' => 'randommes: invalid args',
				'_msg_file_err' => 'randommes: file not found.',
				'_msg_page_err' => 'randommes: page not found.',
				'_msg_auth_err1' => 'randommes: page access denied.',
				'_msg_auth_err2' => "randommes: now, you can\'t access."
			)
		);
	}
	return $msg;
}

// インラインプラグインの出力
function plugin_randommes_inline() {
	$args = func_get_args();
	array_pop($args);
	return call_user_func_array('plugin_randommes_convert', $args);
}

// プラグインの出力
function plugin_randommes_convert() {
	global $script, $vars, $_randommes_messages;

	$num = func_num_args();
	$tags = func_get_args();

	// 引数は1個（必須）～3個
	if(($num < 1) or ($num > 3)){
		return($_randommes_messages['_msg_args_err']);
	}

	// 表示モードの設定
	$mode = 'always';
	if($num >= 2) {
		if(($tags[1] == 'always') or ($tags[1] == 'daily')) {
			$mode = $tags[1];
		}
	}

	// 読み込みモードの設定
	$rmode = 'file';
	if($num == 3) {
		if($tags[2] == 'page') {
			$rmode = 'page';
		}
	}

	if($rmode == 'file') {
		// テキストファイルのチェック
		$target = PLUGIN_RANDOMMES_FILE_DIR.basename($tags[0]);
		if(!file_exists($target)) {
			return($_randommes_messages['_msg_file_err']);
		}
	}
	else if($rmode == 'page') {
		if (!is_page($tags[0])) {
			return($_randommes_messages['_msg_page_err']);
		}

		// 認証モードのチェック
		switch (PLUGIN_RANDOMMES_AUTH_MODE) {
			case 'read':
				break;

			case 'check1':
				if(function_exists('check_readable')) {
					if(!check_readable($tags[0], FALSE, FALSE)) {
						return ($_randommes_messages['_msg_auth_err2']);
					}
				}
				break;

			case 'check2':
				if(function_exists('check_readable')) {
					if(!check_readable($tags[0], TRUE, FALSE)) {
						return ($_randommes_messages['_msg_auth_err1']);
					}
				}
				break;

			case 'noread':
			default:
				if(!plugin_randommes_checkAuth($tags[0])) {
					return ($_randommes_messages['_msg_auth_err1']);
				}
				break;
		}

		$target = $tags[0];
	}

	return(plugin_randommes_gettext($target, $mode, $rmode));
}

function plugin_randommes_gettext($target, $mode, $rmode) {
	// ファイルの読み込み
	if($rmode == 'file') {
		// テキストファイルの読み込み
		$randomtext = file($target);
	}
	else if ($rmode == 'page') {
		// ページの読み込み
		$randomtext = get_source($target);
		if(is_freeze($target)) {
			array_shift($randomtext);
		}
	}

	// ランダムシードの設定
	if($mode == 'always') {
		list($usec, $sec) = explode(' ', microtime());
		srand((float)$sec + ((float)$usec * 100000));
	}
	else if($mode == 'daily') {
		$ts = time();
		$s = mktime(0, 0, 0, date("m", $ts), date("d", $ts)-1, date("Y", $ts));
		srand($s);
	}

	// 乱数の生成
	$randval = rand(0, (count($randomtext) -1));

	// テキストファイルから1行出力
	$ret = $randomtext[$randval];
	$ret = preg_replace("/\x0D\x0A|\x0D|\x0A/", "\n", $ret);
	$ret = convert_html($ret);

	return($ret);
}

// スキンからの呼び出し
function plugin_randommes_ret4skin($target='', $mode='', $rmode='') {
	global $_randommes_messages;
	plugin_randommes_init();

	// 引数は1個（必須）～3個
	if($target == ''){
		return($_randommes_messages['_msg_args_err']);
	}

	// 表示モードの設定
	if(($mode != 'always') and ($mode != 'daily')) {
		$mode = 'always';
	}

	// 読み込みモードの設定
	if(($rmode != 'file') and ($rmode != 'page')) {
		$rmode = 'file';
	}

	if($rmode == 'file') {
		// テキストファイルのチェック
		$target = PLUGIN_RANDOMMES_FILE_DIR.basename($target);
		if(!file_exists($target)) {
			return($_randommes_messages['_msg_file_err']);
		}
	}
	else if($rmode == 'page') {
		if (!is_page($target)) {
			return($_randommes_messages['_msg_page_err']);
		}

		// 認証モードのチェック
		switch (PLUGIN_RANDOMMES_AUTH_MODE) {
			case 'read':
				break;

			case 'check1':
				if(function_exists('check_readable')) {
					if(!check_readable($target, FALSE, FALSE)) {
						return ($_randommes_messages['_msg_auth_err2']);
					}
				}
				break;

			case 'check2':
				if(function_exists('check_readable')) {
					if(!check_readable($target, TRUE, FALSE)) {
						return ($_randommes_messages['_msg_auth_err1']);
					}
				}
				break;

			case 'noread':
			default:
				if(!plugin_randommes_checkAuth($target)) {
					return ($_randommes_messages['_msg_auth_err']);
				}
				break;
		}

	}

	return(plugin_randommes_gettext($target, $mode, $rmode));

}

// 閲覧認証対象ページか？
function  plugin_randommes_checkAuth($page) {
	global $read_auth, $read_auth_pages;
	global $auth_users, $auth_method_type;
	
	if(!isset($read_auth)) {	// read_auth で1.3 チェック
		return true;
	}

	// 閲覧認証フラグをチェック
	if($read_auth == 0) {	// チェックしない
		return true;
	}

	// 認証の要否判断用文字列を取得
	$target_str = '';

	// チェック方法
	if($auth_method_type == 'pagename') {
		$target_str = $page;
	}
	else if ($auth_method_type == 'contents') {
		$target_str = join('',get_source($page));
	}

	// 閲覧認証対象パターンで検索
	foreach($read_auth_pages as $key=>$val) {
		if (preg_match($key, $target_str)) {
			return false;
		}
	}

	// 対象ページではない
	return true;
}

