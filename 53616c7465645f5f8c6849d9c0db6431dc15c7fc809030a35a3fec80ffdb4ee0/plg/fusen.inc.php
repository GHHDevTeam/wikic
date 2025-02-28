<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// fusen.inc.php
// 付箋プラグイン
// ohguma@rnc-com.co.jp
//
// v 1.0 2005/03/12 初版
// v 1.1 2005/03/16 FUSEN_SCRIPT_FILEにDATA_HOME追加,
//                  付箋削除時に線消去,
//                  付箋中での#fusen削除,
//                  付箋中にformがある場合の不具合解消.
// v 1.2 2005/03/16 XSS対策,削除確認追加
// v 1.3 2005/03/17 XHTML1.1対応?
//                  背景透明の設定不備修正
// v 1.4 2005/03/18 検索機能追加,
//                  付箋更新時に添付元ファイルの最終更新日を更新
// v 1.5 2005/03/18 検索機能修正(convert_html後の表示内容で検索),
//                  付箋更新時にRecentChangesを反映,
//                  XSS対策の修正
// v 1.6 2005/03/28 新規追加時のID付与の問題修正
// v 1.7 2005/04/02 HELP修正,入力画面変更
//                  付箋データ保持方法変更
// v 1.8 2005/04/03 付箋が0枚になった際のバグ修正
//                  AJAX対応
// v 1.9 2005/04/13 リアルタイム更新用パラメータをPHP側で設定するよう変更
//                  Opera対応(Opera時リアルタイム更新しない)
// v 1.10 2005/05/10 JSONデータのうち、マルチバイト文字を%uxxxx形式に変換するよう関数追加
//

/////////////////////////////////////////////////
// fusen.jsのPATH
define('FUSEN_SCRIPT_FILE', DATA_HOME . 'skin/fusen.js');

// ロック・解除時の管理者パスワード
define('FUSEN_ADMIN_PASSWORD','aaa');

// 付箋データ用添付ファイル名
define('FUSEN_ATTACH_FILENAME','fusen.dat');

// 付箋枠線スタイル
// 通常分
define('FUSEN_STYLE_BORDER_NORMAL', '#000000 1px solid');
// ロック分
define('FUSEN_STYLE_BORDER_LOCK', '#000000 3px double');
// 削除分
define('FUSEN_STYLE_BORDER_DEL', '#333333 1px dotted');

// リアルタイム更新タイミング(秒)
define('FUSEN_RELOAD_INTERVAL', 3);

/////////////////////////////////////////////////
function plugin_fusen_convert() {
	global $script,$vars;

	// 初期化
	$refer = $vars['page'];
	$jsfile = FUSEN_SCRIPT_FILE;
	$border_normal = FUSEN_STYLE_BORDER_NORMAL;
	$border_lock = FUSEN_STYLE_BORDER_LOCK;
	$border_del = FUSEN_STYLE_BORDER_DEL;
	$interval = FUSEN_RELOAD_INTERVAL * 1000;
	$json = plugin_fusen_getjson(plugin_fusen_data($refer));

	return <<<EOD
<script type="text/javascript" src="$jsfile"></script>
<div class="fusen_menu">
<form action="" onsubmit="return false;">
  <p>
    付箋機能
    [<a href="JavaScript:fusen_new()" title="新しい付箋を作る">新規</a>]
    [<a href="JavaScript:fusen_dustbox()" title="ゴミ箱">ゴミ箱</a>]
    [<a href="JavaScript:fusen_show('fusen_help')" title="使い方">ヘルプ</a>]&nbsp;
    検索：<input type="text" onkeyup="JavaScript:fusen_grep(this.value)" />
  </p>
</form>
</div>
<div id="fusen_help" style="position: absolute; font-size: 11px; left: 90px; top: 80px; padding: 4px; background-color: white; border: black 2px solid; visibility: hidden; z-index: 4; filter:alpha(opacity=90); -moz-opacity:0.9;">
  [<a href="javascript:fusen_hide('fusen_help')">×</a>]
  <ul>
    <li>書き込むと、付箋が表示されます。</li>
    <li>付箋はドラッグして位置を移動できます。</li>
    <li>"set"を押すと、移動した位置を記録できます。付箋毎に登録してください。</li>
    <li>"edit"を押すと、その付箋の編集画面を出します。</li>
    <li>"lock"を押すと、編集・移動を禁止します。lockした付箋は"unlock"で元に戻せます。</li>
    <li>"del"を押すと、付箋をゴミ箱へ移動します。ゴミ箱の付箋は"recover"で元に戻せます。<br />
        ゴミ箱の付箋で"del"を押すと、付箋を完全に削除します。</li>
  </ul>
  <dl>
    <dt>[新規]</dt>
    <dd>新しい付箋の編集画面を表示します。</dd>
    <dt>[ゴミ箱]</dt>
    <dd>ゴミ箱に入れられた付箋を表示します。</dd>
    <dt>[ヘルプ]</dt>
    <dd>この説明書きを表示します。</dd>
    <dt>検索</dt>
    <dd>入力したキーワードを持つ付箋のみ表示します。</dd>
  </dl>
</div>
<script type="text/javascript">
fusenObj = {$json};
fusenBorderObj = {"normal":"{$border_normal}", "lock":"{$border_lock}", "del":"{$border_del}"};
fusenInterval = {$interval};
</script>
<div id="fusen_editbox" style="position: absolute; left: 100px; top: 100px; border: black 2px solid; color: #000000; background-color: #cccccc; padding: 4px;  z-index: 3; visibility: hidden; filter:alpha(opacity=90); -moz-opacity:0.9;">
  [<a href="javascript:fusen_editbox_hide()">×</a>]
  <form id="edit_frm" method="post" action="index.php" style="padding:0; margin:0" onsubmit="this.mode.value='edit'">
    <p style="margin:0">
      文字色：<select id="edit_tc" name="tc" size="1">
        <option id="tc000000" value="#000000" style="color: #000000" selected>■黒</option>
        <option id="tc999999" value="#999999" style="color: #999999">■灰</option>
        <option id="tcff0000" value="#ff0000" style="color: #ff0000">■赤</option>
        <option id="tc00ff00" value="#00ff00" style="color: #00ff00">■緑</option>
        <option id="tc0000ff" value="#0000ff" style="color: #0000ff">■青</option>
      </select>
      <br />
      背景色：<select id="edit_bg" name="bg" size="1">
        <option id="bgffffff" value="#ffffff" style="background-color: #ffffff" selected>白</option>
        <option id="bgffaaaa" value="#ffaaaa" style="background-color: #ffaaaa">薄赤</option>
        <option id="bgaaffaa" value="#aaffaa" style="background-color: #aaffaa">薄緑</option>
        <option id="bgaaaaff" value="#aaaaff" style="background-color: #aaaaff">薄青</option>
        <option id="bgffffaa" value="#ffffaa" style="background-color: #ffffaa">薄黄</option>
        <option id="bgtransparent" value="#transparent">透明</option>
      </select>
      <br />
      線を引く：<input type="text" name="ln" id="edit_ln"/><br />
      <textarea name="body" id="edit_body" cols="40" rows="10"></textarea><br />
      <input type="submit" value="書き込み" />
      <input type="hidden" name="id" id="edit_id"/>
      <input type="hidden" name="z" id="edit_z" value="1" />
      <input type="hidden" name="l" id="edit_l" />
      <input type="hidden" name="t" id="edit_t" />
      <input type="hidden" name="pass" id="edit_pass" value="" />
      <input type="hidden" name="mode" id="edit_mode" value="edit" />
      <input type="hidden" name="plugin" id="edit_plugin" value="fusen" />
      <input type="hidden" name="refer" id="edit_refer" value="{$refer}" />
      <input type="hidden" name="page" id="edit_page" value="{$refer}" />
    </p>
  </form>
</div>

EOD;
}


/////////////////////////////////////////////////
function plugin_fusen_action() {
	global $post;

	// 初期化
	if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');

	$refer = $post['refer'];
	$dat =  plugin_fusen_data($refer);

	// 付箋データの取得
	if ($post['mode']=='getdata') {
		$json = plugin_fusen_getjson($dat);
		ob_clean();
		//$to = (strtolower(mb_http_output())=='pass') ? mb_internal_encoding() : mb_http_output();
		//$json = mb_convert_encoding($json, $to);
		//header ('Content-type: text/plain;charset=' . $to) ;
		header ('Content-type: text/plain') ;
		header ('Content-Length: '. strlen($json));
		echo $json;
		exit;
	}

	$id = preg_replace('/id/', '', $post['id']);

	// ID確定,データ取得
	switch ($post['mode']) {
		case 'set':
		case 'del':
		case 'lock':
		case 'unlock':
		case 'recover';
			if (!array_key_exists($id,$dat)) die_message('The data is not accumulated just.');

			//値更新
			switch ($post['mode']) {
				case 'set':
					$dat[$id]['x'] = (preg_match('/^\d+$/', $post['l']) ? $post['l'] : '');
					$dat[$id]['y'] = (preg_match('/^\d+$/', $post['t']) ? $post['t'] : '');
					$dat[$id]['z'] = (preg_match('/^\d+$/', $post['z']) ? $post['z'] : '');
					break;
				case 'lock':
					$dat[$id]['lk'] = true;
					break;
				case 'unlock':
					$dat[$id]['lk'] = false;
					break;
				case 'del':
					if (!isset($dat[$id]['del'])) {
						$dat[$id]['del'] = true;
						$dat[$id]['lock'] = false;
						$dat[$id]['ln'] = '';
					} else {
						unset($dat[$id]);
					}
					foreach($dat as $k=>$v) {
						if ($dat[$k]['ln'] == 'id'.$id) $dat[$k]['ln'] = '';
					}
					break;
				case 'recover':
					unset($dat[$id]['del']);
			}
			break;
		case 'edit':
			if ($id == '') {
				krsort($dat);
				$id = array_shift(array_keys($dat)) + 1;
			} else {
				if (!array_key_exists($id,$dat)) die_message('The data is not accumulated just.');
			}
			$dat[$id] = array(
				'ln' => (preg_match('/^(id)?(\d+)$/', $post['ln'], $ma) ? $ma[2] : ''),
				'x' => (preg_match('/^\d+$/', $post['l']) ? $post['l'] : ''),
				'y' => (preg_match('/^\d+$/', $post['t']) ? $post['t'] : ''),
				'z' => 1,
				'tc' => (preg_match('/^#[\dA-F]{6}$/i', $post['tc']) ? $post['tc'] : '#000000'),
				'bg' => (preg_match('/^(#[\dA-F]{6}|transparent)$/i', $post['bg']) ? $post['bg'] : '#ffffff'),
				'lk' => false,
				'txt' => $post['body'],
			);
			break;
		default:
			die_message('Illegitimate parameter was used.');
	}

	if (!preg_match('/^(lock|unlock)$/', $post['mode']) || 
	    (preg_match('/^(lock|unlock)$/', $post['mode']) && $post['pass']==FUSEN_ADMIN_PASSWORD)) {
		//書き込み
		$fname = UPLOAD_DIR . encode($refer) . '_' . encode(FUSEN_ATTACH_FILENAME);
		if (count($dat)>0) {
			$fp = fopen($fname, "w");
			flock($fp, LOCK_EX);
			fputs($fp, FUSEN_ATTACH_FILENAME . "\n");
			fputs($fp, serialize($dat));
			fclose($fp);
		} else {
			if (file_exists($fname)) unlink($fname);
		}
		//添付元の更新
		$fname = DATA_DIR . encode($refer) . '.txt';
		if (file_exists($fname)) touch($fname);
		put_lastmodified();
	}

	if ($post['mode']!='set') {
		pkwk_headers_sent();
		header('Location: ' . get_script_uri() . '?' . rawurlencode($refer));
	} else {
		//setの際は何も返さない
		ob_clean();
	}
	exit;
}

/////////////////////////////////////////////////
//添付ファイル読み込み
function plugin_fusen_data($page) {
	$fname = encode($page) . '_' . encode(FUSEN_ATTACH_FILENAME);
	if (!file_exists(UPLOAD_DIR . $fname)) return array();
	$data = file(UPLOAD_DIR . $fname);
	if ($data[0] != FUSEN_ATTACH_FILENAME . "\n") return array();
	return unserialize(join('',array_slice($data,1)));
}

/////////////////////////////////////////////////
//PHPオブジェクトをJSONへ変換
function plugin_fusen_getjson($fusen_data) {
	$json = '{';

	// 付箋・線データ作成
	foreach ($fusen_data as $k => $dat) {
		//付箋番号が数字でない場合は飛ばす。
		if (!preg_match('/\d+/', $k)) continue;
		$id = 'id' . $k;

		//#fusenプラグインのネスト禁止
		$dat['txt'] = preg_replace('/^\#fusen\b/m', '', $dat['txt']);

		// XSS対策(付箋データが直接改ざんされる事態も想定)
		if (!preg_match('/^\d+$/', $dat['x'])) $dat['x'] = 100 + $k;
		if (!preg_match('/^\d+$/', $dat['y'])) $dat['y'] = 100 + $k;
		if (!preg_match('/^\d+$/', $dat['z'])) $dat['z'] = 1;
		if (!preg_match('/^#[\dA-F]{6}$/i', $dat['tc'])) $dat['tc'] = '#000000';
		if (!preg_match('/^(#[\dA-F]{6}|transparent)$/i', $dat['bg'])) $dat['bg'] = '#ffffff';
		if (!preg_match('/^(id)?\d+$/', $dat['ln'])) $dat['ln'] = '';

		// JSONの構成
		if ($json != '{') $json .= ",\n  ";
		$json .=  $k . ':{';
		$json .= '"x":' . $dat['x'] . ',';
		$json .= '"y":' . $dat['y'] . ',';
		$json .= '"z":' . $dat['z'] . ',';
		$json .= '"tc":"' . $dat['tc'] . '",';
		$json .= '"bg":"' . $dat['bg'] . '",';
		$json .= '"disp":"' . plugin_fusen_jsencode(convert_html($dat['txt'])) . '",';
		$json .= '"txt":"' . plugin_fusen_jsencode(htmlspecialchars($dat['txt'])) . '"';
		if (isset($dat['ln']) && $dat['ln']) $json .= ',"ln":' . preg_replace('/^id/', '', $dat['ln']) ;
		if (isset($dat['lk']) && $dat['lk']) $json .= ',"lk":' . $dat['lk'];
		if (isset($dat['del']) && $dat['del']) $json .= ',"del":' . $dat['del'] ;
		$json .= '}';
	}
	$json .= '}';
	return $json;
}

/////////////////////////////////////////////////
//JSON向けエンコード
function plugin_fusen_jsencode($str) {
	$str = plugin_fusen_js_escape($str);
	$str = preg_replace('/(\x22|\x2F|\x5C)/', '\\\$1', $str);
	$str = preg_replace('/\x08/', '\b', $str);
	$str = preg_replace('/\x09/', '\t', $str);
	$str = preg_replace('/\x0A/', '\n', $str);
	$str = preg_replace('/\x0C/', '\f', $str);
	$str = preg_replace('/\x0D/', '\r', $str);
	return $str;
}

function plugin_fusen_js_escape($str) {
	$len = mb_strlen($str);
	$x = "";
	for ($i=0; $i<$len; $i++) {
		$char = mb_substr($str,$i,1);
		if (ord($char) < 0x7F) {
			$x .= $char;
		} else {
			$x .= '%u' . bin2hex(mb_convert_encoding($char, "UCS-2"));
		}
	}
	return $x;
}
?>
