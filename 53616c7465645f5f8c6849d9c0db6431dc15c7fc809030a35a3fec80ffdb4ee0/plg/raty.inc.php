<?php
// $Id: oxy.inc.php,v 0.61 2021/02/20 08:01:24 oxy Exp $

/** 
* @link https://oxynotes.com/?p=10360
* @author oxy
* @license http://www.gnu.org/licenses/gpl.ja.html GPL
*/

/**
 *
 * ratyプラグイン raty.inc.php
 * jQuery Ratyを利用した5段階評価
 * jQuery Raty：https://github.com/wbotelhos/raty
 * サイト全体でjQueryを使っているならpukiwiki.skin.php側でjQueryを読み込む
 * このプラグインでのみjQueryを使う場合はplugin_raty_init()のコメントアウトを削除する
 * 
 * 画像とデザインを適用するには以下のものを追加
 * imageフォルダに「star-on.png、star-off.png、star-half.png」
 * skinフォルダに「raty.css」
 * 
 * 1.クリックで5段階評価
 * 1.平均値と総投票数を表示
 * 1.ページ内に複数設置可能
 * 1.引数にallでページ内の全ての評価の平均値と総投票数を表示 v.0.2～
 * 1.引数にonceで一度だけ評価を許可する v.0.2～
 * 1.Cookieが無効な場合は投稿禁止
 * 1.Cookieを利用した連投の禁止（デフォルトで3日間）
 *
 * version 0.1 2014-09-15
 * version 0.2 2016-04-22
 * version 0.3 2016-05-09 Cookie名にencode()を使ってエスケープを追加
 * version 0.3.1 2017-08-05 スキンによっては動作しないため $(function raty<num>() { の記述を削除
 * version 0.4 2018-02-04 複数のページを表示するプラグインで、複数表示されている場合に対応
 * version 0.4.2 2018-06-01 引数に1から5以外の数字を入れた場合エラー表示
 * version 0.5 2018-06-01 インライン型に対応
 * version 0.6 2019-05-29 PHP7.3に対応（NAN表示の修正）
 * version 0.6.1 2021-02-20 PHP7.4に対応（NAN表示の修正）byK
 */




/**
 * ratyプラグインが初回アクセス時のみ実行される（plugin_プラグイン名_init）
 * このプラグイン以外でjQueryを使わない場合はここを有効にする
 * pukiwikiのバージョンによってはプラグインが呼び出されるごとに複数回実行されるバグがあるので注意。1.5.1で修正済み
 */
function plugin_raty_init()
{
	global $head_tags, $jquery;
	// jQueryを使っていない場合はここを有効にする
    if(!isset($jquery)){
        $head_tags[] = '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>';
        $jquery = true;
    }
	$head_tags[] = "<link rel='stylesheet' type='text/css' media='screen' href='../53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plgfiles/css/raty.css' charset='utf-8'>";
}




/**
 * ratyプラグインのメイン関数（plugin_プラグイン名_convert）
 * ブロック形式「#raty()」で記述した際に実行される
 * 
 * 1.GETメソッドで渡される引数をグローバルの$varsから取得
 * 2.整形して平均値を出す
 * 3.html_convertに平均値を渡す
 */
function plugin_raty_convert()
{
	// プラグインの引数等を取得するためグローバル変数読込み
	global $vars, $defaultpage;

	// まずは指定の仕方が正しいかバリデーション
	// こちらは引数からはできないのでget_sourceでページの情報を取得
	$lines = get_source( $vars['page'] );

	// #ratyという記述があるのに()がない場合にエラーメッセージ
	foreach($lines as $line)
	{
		if ( preg_match( '/^#raty/', $line ) ) {
			if ( preg_match( '/^#raty\((.*)\)\s*$/', $line, $matches ) ) {
			} else {
				$msg = "ratyプラグインの記述が正しくありません";
				return $msg;
			}
		}
	}

	// 投稿時にCookieが有効かチェック
	$cookie_name = 'raty_cookie_check';
	if ( ! isset( $_COOKIE[$cookie_name] ) ) {
		$matches = array();
		preg_match( '!(.*/)!', $_SERVER['REQUEST_URI'], $matches ); //ドメイン取得
		setcookie( $cookie_name, 1, time()+(60*60), $matches[0] ); // 引数はそれぞれ「Cookie名、Cookie値、有効期限、ドメイン」
	}

	// デフォルト値の指定
	$count = 0;
	$once = 0;
	$all = 0;
	$all_count_and_per = 0;

	// プラグインから引数を取得
	$args = func_get_args();

	// 引数の整形
	$args = h($args);
	$vowels = array("[", "]");
	$args = str_replace( $vowels, "", $args );

	if( $args[0] == "once" ){ // 1回だけ評価するタイプの場合

		// 第2引数を調べる（1番目はonceなので調べない）
		if( $args[1] == 1 || $args[1] == 2 || $args[1] == 3 || $args[1] == 4 || $args[1] == 5 || $args[1] == null ){ // 引数のバリデーション
			if( $args[1] == null ){	// 評価がまだの場合は0を代入
				$ave_num = 0;
			} else {
				$ave_num = $args[1];
			}
			$once = 1;
			$html = html_convert( $ave_num, $count, $once, $all, $all_count_and_per );
		} else {
			$msg = "ratyプラグインの引数が正しくありません";
			return $msg;
		}

	} elseif( $args[0] == "all" ) { // ページ全体の評価の平均を出すタイプ（引数は無し）

		// get_source()で取得したページの情報を元に全ての評価を取得・カウントする
		foreach( $lines as $line ) {
			if ( preg_match( '/[#&]raty\((.*)\)/', $line, $matches ) ) {
				$match_vars = h( $matches[1] );
				$vowels = array( "[", "]", ",", "once", "all" ); // 配列からいらない文字を削除
				$match_var_arg .= str_replace( $vowels, "", $match_vars );
			}
		}

		$match_var_arg = str_split( $match_var_arg );
		if( $match_var_arg[0] == 0 ){ // str_split()だと何もなくてもarrayが1で0が入るので対策
			$ave_num = 0;
		} else {
			$count = count( $match_var_arg );
			$ave_num = array_sum( $match_var_arg ) / $count; // 平均値
			$ave_num = round( $ave_num, 1 ); // 小数点第二位で丸める
		}

		// 値が無い場合もあるのでデフォルト値0を入れておく
		$count_1 = $count_2 = $count_3 = $count_4 = $count_5 = 0;

		foreach( $match_var_arg as $val ){
			if( $val == "1"){
				$count_1++;
			} elseif( $val == "2") {
				$count_2++;
			} elseif( $val == "3") {
				$count_3++;
			} elseif( $val == "4") {
				$count_4++;
			} elseif( $val == "5") {
				$count_5++;
			}
		}

		if ( $count !== 0 ) { // NAN対策
			$count_1_per = 100 / $count * $count_1; // 全体の割合からカウントの割合を出す（CSS用）
			$count_2_per = 100 / $count * $count_2;
			$count_3_per = 100 / $count * $count_3;
			$count_4_per = 100 / $count * $count_4;
			$count_5_per = 100 / $count * $count_5;

			$count_1_per = round( $count_1_per, 1 ); // 小数点第二位で丸める
			$count_2_per = round( $count_2_per, 1 );
			$count_3_per = round( $count_3_per, 1 );
			$count_4_per = round( $count_4_per, 1 );
			$count_5_per = round( $count_5_per, 1 );
		} else {
			$count_1_per = $count_2_per = $count_3_per = $count_4_per = $count_5_per = 0;
		}

		// それぞれのカウント数と割合を入れた配列を作成
		$all_count_and_per = array(
			'count_1_per' => $count_1_per,
			'count_1' => $count_1,
			'count_2_per' => $count_2_per,
			'count_2' => $count_2,
			'count_3_per' => $count_3_per,
			'count_3' => $count_3,
			'count_4_per' => $count_4_per,
			'count_4' => $count_4,
			'count_5_per' => $count_5_per,
			'count_5' => $count_5
		);

		$all = 1; // allが有効
		$html = html_convert( $ave_num, $count, $once, $all, $all_count_and_per );

	} else { // 複数回評価して平均値を出すタイプ

		// しつこいけど、想定外の間違いをすることも想定して数値でバリデーション
		foreach( $args as $val ){
			if( $val < 1 || $val > 5 || $val = "" ){ // 引数に使えるのは1から5まで
				$msg = "ratyプラグインの引数が正しくありません";
				return $msg;
			}
		}
		$count = count( $args );
		$ave_num = array_sum( $args ) / $count; // 平均値
		$ave_num = round( $ave_num, 1 ); // 小数点第二位で丸める
		$html = html_convert( $ave_num, $count, $once, $all, $all_count_and_per );
	}

	return $html;
}

/**
 * インライン形式「&raty();」で記述した際に実行される
 * 
 * 1.GETメソッドで渡される引数をグローバルの$varsから取得
 * 2.整形して平均値を出す
 * 3.html_convertに平均値を渡す
 */
function plugin_raty_inline()
{
	// プラグインの引数等を取得するためグローバル変数読込み
	global $vars, $defaultpage;

	// まずは指定の仕方が正しいかバリデーション
	// こちらは引数からはできないのでget_sourceでページの情報を取得
	$lines = get_source( $vars['page'] );

	// #ratyという記述があるのに()がない場合にエラーメッセージ
	foreach($lines as $line)
	{
		if ( preg_match( '/&raty/', $line ) ) {
			if ( preg_match( '/&raty\((.*)\);/', $line, $matches ) ) {
			} else {
				$msg = "ratyプラグインの記述が正しくありません";
				return $msg;
			}
		}
	}

	// 投稿時にCookieが有効かチェック
	$cookie_name = 'raty_cookie_check';
	if ( ! isset( $_COOKIE[$cookie_name] ) ) {
		$matches = array();
		preg_match( '!(.*/)!', $_SERVER['REQUEST_URI'], $matches ); //ドメイン取得
		setcookie( $cookie_name, 1, time()+(60*60), $matches[0] ); // 引数はそれぞれ「Cookie名、Cookie値、有効期限、ドメイン」
	}

	// デフォルト値の指定
	$count = 0;
	$once = 0;
	$all = 0;
	$all_count_and_per = 0;

	// プラグインから引数を取得
	$args = func_get_args();
	array_pop( $args ); // インライン型は第二引数が自動でつくため削除
	// 引数の整形
	$args = h($args);
	$vowels = array("[", "]");
	$args = str_replace( $vowels, "", $args );

	if( $args[0] == "once" ){ // 1回だけ評価するタイプの場合

		// 第2引数を調べる（1番目はonceなので調べない）
		if( $args[1] == 1 || $args[1] == 2 || $args[1] == 3 || $args[1] == 4 || $args[1] == 5 || $args[1] == null ){ // 引数のバリデーション
			if( $args[1] == null ){	// 評価がまだの場合は0を代入
				$ave_num = 0;
			} else {
				$ave_num = $args[1];
			}
			$once = 1;
			$html = html_convert( $ave_num, $count, $once, $all, $all_count_and_per );
		} else {
			$msg = "ratyプラグインの引数が正しくありません";
			return $msg;
		}

	} elseif( $args[0] == "all" ) { // ページ全体の評価の平均を出すタイプ（引数は無し）

		// get_source()で取得したページの情報を元に全ての評価を取得・カウントする
		foreach( $lines as $line ) {
			if ( preg_match( '/[#&]raty\((.*)\)/', $line, $matches ) ) {
				$match_vars = h( $matches[1] );
				$vowels = array( "[", "]", ",", "once", "all" ); // 配列からいらない文字を削除
				$match_var_arg .= str_replace( $vowels, "", $match_vars );
			}
		}

		$match_var_arg = str_split( $match_var_arg );
		if( $match_var_arg[0] == 0 ){ // str_split()だと何もなくてもarrayが1で0が入るので対策
			$ave_num = 0;
		} else {
			$count = count( $match_var_arg );
			$ave_num = array_sum( $match_var_arg ) / $count; // 平均値
			$ave_num = round( $ave_num, 1 ); // 小数点第二位で丸める
		}

		// 値が無い場合もあるのでデフォルト値0を入れておく
		$count_1 = $count_2 = $count_3 = $count_4 = $count_5 = 0;

		foreach( $match_var_arg as $val ){
			if( $val == "1"){
				$count_1++;
			} elseif( $val == "2") {
				$count_2++;
			} elseif( $val == "3") {
				$count_3++;
			} elseif( $val == "4") {
				$count_4++;
			} elseif( $val == "5") {
				$count_5++;
			}
		}

		if ( $count !== 0 ) { // NAN対策
			$count_1_per = 100 / $count * $count_1; // 全体の割合からカウントの割合を出す（CSS用）
			$count_2_per = 100 / $count * $count_2;
			$count_3_per = 100 / $count * $count_3;
			$count_4_per = 100 / $count * $count_4;
			$count_5_per = 100 / $count * $count_5;

			$count_1_per = round( $count_1_per, 1 ); // 小数点第二位で丸める
			$count_2_per = round( $count_2_per, 1 );
			$count_3_per = round( $count_3_per, 1 );
			$count_4_per = round( $count_4_per, 1 );
			$count_5_per = round( $count_5_per, 1 );
		} else {
			$count_1_per = $count_2_per = $count_3_per = $count_4_per = $count_5_per = 0;
		}
        echo $count_1_per;
		// それぞれのカウント数と割合を入れた配列を作成
		$all_count_and_per = array(
			'count_1_per' => $count_1_per,
			'count_1' => $count_1,
			'count_2_per' => $count_2_per,
			'count_2' => $count_2,
			'count_3_per' => $count_3_per,
			'count_3' => $count_3,
			'count_4_per' => $count_4_per,
			'count_4' => $count_4,
			'count_5_per' => $count_5_per,
			'count_5' => $count_5
		);

		$all = 1; // allが有効
		$html = html_convert( $ave_num, $count, $once, $all, $all_count_and_per );

	} else { // 複数回評価して平均値を出すタイプ

		// しつこいけど、想定外の間違いをすることも想定して数値でバリデーション
		foreach( $args as $val ){

			if( $val < 1 || $val > 5 || $val = "" ){ // 引数に使えるのは1から5まで
				$msg = "ratyプラグインの引数が正しくありません";
				return $msg;
			}
		}
		$count = count( $args );
		$ave_num = array_sum( $args ) / $count; // 平均値
		$ave_num = round( $ave_num, 1 ); // 小数点第二位で丸める
		$html = html_convert( $ave_num, $count, $once, $all, $all_count_and_per );
	}

	return $html;
}




/**
 * jQuery RatyのHTML部分の記述
 * 1.複数回評価するタイプ
 * 2.一度のみ評価するタイプ
 * 3.すべての評価をグラフ表示するタイプ
 * 以上の3つのタイプに対応している
 * 
 * @param $ave_num int 評価の平均値
 * @param $count int 評価の数
 * @param $once 1|0 一度のみ評価するタイプか判別
 * @param $all 1|0 全体の評価をグラフ表示するタイプか判別
 * @param $all_count_and_per array 1～5までの評価の数と平均値の配列
 * return ratyプラグインを表示するためのhtml
 */
function html_convert( $ave_num, $count, $once, $all, $all_count_and_per )
{
	// ページ名取得用にグローバル変数読込み
	global $vars, $digest;
	static $page_old = ""; // ページ名用 静的変数（初回時だけ初期化される）
	static $raty_id = 0; // 1ページ中に何回使われているかカウント用
	static $raty_page_id = 0; // 複数のページを表示するようなプラグイン時のカウント用
	$all_graf = '';
	$form = '';
	$page = isset($vars['page']) ? $vars['page'] : $defaultpage;

	// 前回渡されたページ名が一致する場合は$raty_idを加算
	// （calendarプラグインなど複数のページを1ページに書き出すプラグイン対策）
	if ( $page_old == rawurlencode($page) ) {
		$raty_id++; // 同じページ内で複数回プラグインが読み込まれたら加算する
	} else {
		$raty_id = 0; // 違うページの場合カウントを戻す
		$page_old = rawurlencode($page);
	}

	// プラグインで星をクリックされた時に実行されるURLを作成
	$url = get_script_uri() . '?plugin=raty' .
	'&refer=' . rawurlencode($page) .
	'&digest=' . rawurlencode($digest) .
	'&raty_id=' . $raty_id . 
	'&score=';
	$raty_page_id++;

	// onceの場合と、それ以外の部分を分岐。先に組み立てる
	if( $once == 1 ){
		if( $ave_num == 0 ){
			$once_or = '　'; // 次の行に文字があるとずれるので暫定
		} else {
			$once_or = '評価：' . $ave_num;
		}
	} else {
		$once_or = '評価数：' . $count . '　平均評価：' . $ave_num;
	}

	// allの場合にグラフを作る。先に組み立てる
	if( $all == 1 ){
		$all_graf .= '<div id="raty-table">' . "\n";
		$all_graf .= '<table id="raty-table">' . "\n";
		$all_graf .= '<tbody>' . "\n";

		for( $count = 5; $count > 0; $count-- ){
			$all_graf .= '<tr>' . "\n";
			$all_graf .= '<td>' . "\n";
			$all_graf .= '<p class="raty-graf-comment">星' . $count . 'つ</p>' . "\n";
			$all_graf .= '</td>' . "\n";
			$all_graf .= '<td>' . "\n";
			$all_graf .= '<div class="mater">' . "\n";
			$ccp = 'count_' . $count . '_per';

			// 100パーセントの時にマージンライトを-1解除
			if( $all_count_and_per[$ccp] == 100 ){
				$margin_100per = "margin: 0;";
			} else {
				$margin_100per = "";
			}

			$all_graf .= '<div class="meter-bar" style="width:' . $all_count_and_per[$ccp] . '%;' . $margin_100per . '" >' . "\n";
			$all_graf .= '</div><!-- .meter-bar -->' . "\n";
			$all_graf .= '</div><!-- .mater -->' . "\n";
			$all_graf .= '</td>' . "\n";
			$all_graf .= '<td>' . "\n";
			$all_graf .= '<p class="raty-graf-count">' . $all_count_and_per["count_$count"] . '</p>' . "\n";
			$all_graf .= '</td>' . "\n";
			$all_graf .= '<tr>' . "\n";
		}

		$all_graf .= '</tbody>' . "\n";
		$all_graf .= '</table>' . "\n\n";
		$all_graf .= '</div><!-- #raty-table -->' . "\n";
	}
    // NaN対策 byK
    if(is_nan($ave_num)){
        $ave_num = 0;
    };
    // ----
	// ここから組み立てたものを利用してHTMLを作成
	$form .= '<div class="raty-plugin">';
	$form .= '<script type="text/javascript" src="../53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plgfiles/js/jquery.raty.js"></script>' . "\n";
	$form .= '<div class="raty star' . $raty_page_id . '" "></div><p class="star-after">' . $once_or . '</p>' . "\n";
	$form .= '<script type="text/javascript">' . "\n";
	$form .= '$.fn.raty.defaults.path = "../53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/image/";' . "\n"; // スター画像のへのパスを記述
	$form .= '$(".star' . $raty_page_id . '").raty({' . "\n";
	$form .= 'number: 5,' . "\n";
	$form .= 'score : ' . $ave_num . ',' . "\n";

	// onceで既に評価済みのの場合とallの場合に再評価不可
	if( $once == 1 && $ave_num !== 0 || $all == 1 ){
		$form .= 'readOnly: true,' . "\n";
	}

	$form .= "hints: ['1', '2', '3', '4', '5']," . "\n"; // マウスオーバー時のタイトル
	$form .= 'click: function(score, evt) {' . "\n"; // evtには多くの情報がある。scoreならdelegateTargetのimgのaltなど
	$form .= 'var url' . $raty_page_id . ' = "' . $url . '" + score;' . "\n"; // 作成したURLにクリックされたスコアを追加
	$form .= 'location.href=url' . $raty_page_id . ';' . "\n"; // GETでURL更新
	$form .= 'console.log(url' . $raty_page_id . ')' . "\n";

	$form .= '}' . "\n";
	$form .= '});' . "\n";

	$form .= '</script>' . "\n";
    $form .= $all_graf;
	$form .= '</div><!-- .raty-plugin -->' . "\n";

	return $form;
}




/**
 * クリックされた際に実行される関数（plugin_プラグイン名_action）
 * GETで渡されたパラメータを元にratyのスコアを追加する。
 * その際、タイムスタンプは更新しない。
 * 複数設置されている場合はraty_idを利用して更新する行数を把握する。
 */
function plugin_raty_action()
{
	// 渡される変数等を読み込む
	global $vars;

	// 衝突時のメッセージなど
	global $_title_collided;

	// pukiwikiが閲覧モードの場合は編集不可
	if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');

	// ページに複数ratyが設置されている場合を想定して$raty_id
	// 新しく追加するスコアを$scoreに代入する
	$raty_id = $vars['raty_id'];

	// ページ名を取得
	$page = isset( $vars['refer'] ) ? $vars['refer'] : $defaultpage;

	// Cookieが有効かどうかを調べる（有効であればplugin_raty_convertで埋め込み済み）
	$cookie_name = 'raty_cookie_check';
	if ( ! isset( $_COOKIE[$cookie_name] ) ) {
		return array(
		'msg'  => _('投稿エラー'),
		'body' => _('評価をするにはCookieを有効にしてください。'),
		);
	}

	// 書き込む際に付けられるCookieを既に持っている場合はメッセージを表示（多重投稿用）
	if ( is_continuous_raty( $page, $raty_id ) ) {
		return array(
		'msg'  => _('投稿エラー'),
		'body' => _('評価の連投は禁止しています。'),
		);
	}

	// pukiwiki関数get_sourceで全ての行を$linesに代入
	$lines = get_source( $page );

	// raty_idのバリデーション
	if ( is_numeric( $vars['raty_id'] ) ){
		$score = $vars['raty_id'];
	} else {
		$msg = "raty_idの値が正しくありません";
		return array('msg'=>$msg, 'body'=>"");
	}

	// scoreのバリデーション
	if ( is_numeric( $vars['score'] ) ){
		$score = $vars['score'];
	} else {
		$msg = "scoreの値が正しくありません";
		return array('msg'=>$msg, 'body'=>"");
	}

	// digestのバリデーション（衝突チェック）
	$contents = implode('', $lines);
	if ( md5( $contents ) !== $vars['digest'] ) {
		$msg  = $_title_collided;
		$body = show_preview_form( $_msg_collided, $contents );
		return array('msg'=>$msg, 'body'=>$body);
	}

	$i = 0; // 行カウント用
	$raty_count = 0; // $raty_idカウント用
	$newlines = '';

	foreach( $lines as $line )
	{
		$i++;
		// プラグインの行を調べ、複数ある場合に備えて$vote_idでチェック
		if (preg_match('/^#raty/i', $line, $matches) && $raty_id == $raty_count++ ) {
			preg_match('/\[(.*)\]/', $line, $scoredata); // []で囲まれた引数だけ切り取り
			if ( $scoredata[1] == "" ){
				$line = '#raty([' . $score . '])' . "\n"; // スコアを追加（引数のない場合）
			} else {
				$line = '#raty([' . $scoredata[1] . ',' . $score . '])' . "\n"; // スコアを追加
			}
			$i--;
			array_splice( $lines, $i, 1, $line ); // 旧スコアの行に新スコアを差し替え
			$lines = implode( '', $lines ); // 配列要素を文字列により連結
		}
		$newlines .= $line;
	}
	page_write( $page, $newlines, TRUE ); // ページ名$pageを$linesで書き換え。TRUEでタイムスタンプ更新しない
}




/**
 * 衝突時に表示されるエラー画面
 * 
 * @param $msg str エラーメッセージ
 * @param $msg str pukiwikiから渡される
 */
function show_preview_form( $msg = '', $body = '' )
{
	global $vars, $rows, $cols; // ユーザー設定のテキストボックスのサイズも合わせて取得
	$s_refer  = h( $vars['refer'] );
	$s_digest = h( $vars['digest'] );
	$s_body   = h( $body );
	$form  = '';
	$form .= $msg . "\n";
	$form .= '<form action="' . get_script_uri() . '?cmd=preview" method="post">' . "\n";
	$form .= '<div>' . "\n";
	$form .= ' <input type="hidden" name="refer"  value="' . $s_refer . '" />' . "\n";
	$form .= ' <input type="hidden" name="digest" value="' . $s_digest . '" />' . "\n";
	$form .= ' <textarea name="msg" rows="' . $rows . '" cols="' . $cols . '" id="textarea">' . $s_body . '</textarea><br />' . "\n";
	$form .= '</div>' . "\n";
	$form .= '</form>' . "\n";
	return $form;
}




/**
 * html特殊文字をエスケープ（XSS対策）
 * 
 * @param str エスケープする文字列
 */
function h( $str ){
	if( is_array( $str ) ){
		return array_map( "h", $str );
	}else{
		return htmlspecialchars( $str, ENT_QUOTES, "UTF-8" );
	}
}




/**
 * Cookieのを利用して3日間は同じ項目の評価を禁止（連投規制）
 * 
 * @param str ページ名
 * @param int ページにあるratyの上から○番目という指定
 */
function is_continuous_raty( $page, $raty_id )
{
	$cmd = 'raty';
	$ratykey = $cmd . '_' . encode($page) . '_' . $raty_id;

	// 有効なCookieを持っている場合（前回の投稿から3日以内）
	if ( isset( $_COOKIE[$ratykey] ) ) {
		return true;
	}

	// 有効なCookieを持っていない場合
	$matches = array();
	preg_match( '!(.*/)!', $_SERVER['REQUEST_URI'], $matches ); //ドメイン取得
	setcookie( $ratykey, 1, time()+(60*60*24*3), $matches[0] ); // 引数はそれぞれ「Cookie名、Cookie値、有効期限、ドメイン」
	return false;
}