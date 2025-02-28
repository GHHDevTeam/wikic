<?php
// PukiWiki - Yet another WikiWikiWeb clone
// tableedit.inc.php
// 	2020.Jan.23  Ver 1.2 Haruka Tomose
//    You must add "a-table.js" library.
//
// tabeledit plugin
// http://tomose.dynalias.net/junk/index.php?pukiwiki%A5%D7%A5%E9%A5%B0%A5%A4%A5%F3/tableedit

// ver 1.1 表をpukiwikiから編集するボタン新設。
// ver 1.2 2020.Jan.23 参照権限のない表を表示してしまうセキュリティホールをふさいだ

define('PLUGIN_TABLEEDIT_SIZE_MSG',  140);
define('PLUGIN_TABLEEDIT_FORMAT_MSG',  '$msg');

define('PLUGIN_TABLEEDIT_BTN_UPDATE', "編集完了");
define('PLUGIN_TABLEEDIT_BTN_RETURN', "閉じる");
define('PLUGIN_TABLEEDIT_BTN_START', "WYSIWYGで編集");
define('PLUGIN_TABLEEDIT_BTN_PSTART', "Pukiwikiで編集");
define('PLUGIN_TABLEEDIT_MSG_BACKTO', "下のボタンを押すことでこのページを閉じて元ページを更新します。");
define('PLUGIN_TABLEEDIT_MSG_AGAIN', "下のボタンを押すことで、元ページに戻ります。");
define('PLUGIN_TABLEEDIT_BTN_RELOAD', "最初から");
define('PLUGIN_TABLEEDIT_BTN_CANCEL', "取り止め");

define('PLUGIN_TABLEEDIT_ERR_READ', "|参照権限のない表です。|");


// ----

function plugin_tableedit_action()
{
	global $script, $vars, $now, $_title_updated, $_no_name;
	global $_msg_tabeledit_collided, $_title_collided;
	global $_tableedit_mes;

	if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');

	// 権限チェック
	if(!check_editable($vars['refer'])) return '';

	if (! isset($vars['msg'])) return array('msg'=>'', 'body'=>''); // Do nothing

	$head = '';
	$match = array();
	if ($vars['msg'] == '') return array('msg'=>'', 'body'=>''); // Do nothing

	$postdata  = str_replace('$msg', $vars['msg'], PLUGIN_TABLEEDIT_FORMAT_MSG);

	// 送られてきたソース内、リンク内にある &gt; の置き換えをする。
	$reg=0;
//	tomoseDBG("match pre?". $reg );
	$reg = preg_match('/\[\[(.+?)&gt;(.+?)\]\]/i',$postdata ,$match);
	tomoseDBG("match?". $reg );

	if($reg==1){
		$postdata=preg_replace( '/\[\[(.+?)&gt;(.+?)\]\]/i','[[$1>$2]]',$postdata);
	}
	

//	$postdata=preg_replace( '&gt;','>',$postdata,1);
//	$postdata=preg_replace( '\[\[(.+?)&gt;(.+?)\]\]','\[\[$1>$2\]\]',$postdata);


	$btext=PLUGIN_TABLEEDIT_BTN_RETURN;

	$title = $_title_updated;
	$body = '';

	if (md5(get_source($vars['refer'], TRUE, TRUE)) !== $vars['digest']) {
		$title = $_title_collided;
		$body  = convert_html(PLUGIN_TABLEEDIT_MSG_AGAIN );
		
		$body.=<<<EOD
<input type="button" name="tableedit" value="$btext" onclick='tableedit_close();'/>

<script>
function tableedit_close(){
	if( window.opener){ // メインウィンドウの存在をチェック
		window.opener.location.reload()
	}
	this.close();

}
</script>
EOD;
	//	$body  .= convert_html(make_pagelink($vars['base']));

	}else{
		page_write($vars['refer'], $postdata);
		$body=convert_html(PLUGIN_TABLEEDIT_MSG_BACKTO);

		$body.=<<<EOD
<input type="button" name="tableedit" value="$btext" onclick='tableedit_close();'/>

<script>
function tableedit_close(){
	if( window.opener){ // メインウィンドウの存在をチェック
		window.opener.location.reload()
	}
	this.close();
}
</script>
EOD;

	//	$body  .= convert_html(make_pagelink($vars['base']));

	}

	$retvars['msg']  = $title;
	$retvars['body'] = $body;
	$vars['page'] = $vars['refer'];
	return $retvars;
}

function plugin_tableedit_convert()
{
	global $vars, $digest, $_btn_update, $_btn_name, $_msg_comment,$head_tags;
	global $_tbl_name,$_tbl_path, $_tbl_data;
	global $_tableedit_mes;

	static $comment_cols = PLUGIN_TABLEEDIT_SIZE_MSG;
	static $numbers;

	// JavaScriptを埋め込む都合上、「最初の１つだけ」判定のために個数カウント。
	if (! isset($numbers[$vars['page']])) $numbers[$vars['page']] = 0;
	$comment_no = $numbers[$vars['page']]++;

	// パラメータチェック。
	$options = func_num_args() ? func_get_args() : array();
	//先頭パラメータは表名称。
	$_tbl_name = array_shift($options);
	// 残りのパラメータのチェック。 
	// Fix指定なら「編集禁止」で動作。
	if (in_array('fix', $options)) { $fix = true; }
	else{ $fix=false; }

	//対象の表が現時点で存在するか否かチェック。
	$_tbl_path = DATA_DIR.encode($_tbl_name).".txt";
	$is_file = is_file($_tbl_path);
	if(! $is_file){
		// なかった場合、初期レベルの表を作成する。
		file_put_contents ( $_tbl_path , "|a|b|");
	}
	// 権限チェック。表を表示する権限がないなら、この時点で終了する。
	if(!check_readable($_tbl_name,false,false)){ 
		return convert_html(PLUGIN_TABLEEDIT_ERR_READ); 
	}

	// 権限チェック。表を編集する権限がないなら、fix扱い。
	if(!check_editable($_tbl_name,false,false)) { $fix = true; }




	// 表の表示用データ作成
	$_tbl_data = convert_html(file_get_contents($_tbl_path));
	$_tbl_cashe = CACHE_DIR."tableedit_".encode($_tbl_name).".html";

	$script = get_script_uri();
	$btext=PLUGIN_TABLEEDIT_BTN_START;
	$ptext=PLUGIN_TABLEEDIT_BTN_PSTART;

	if($fix){
		// 編集禁止モード。単純に表データだけを表示する。
$string=<<<EOD
<div>
$_tbl_data
</div>
EOD;

	}else{
		// 編集可能モード。
		// 編集画面用のHTMLをアップデートする。
		plugin_tableedit_UpdateCache($_tbl_name);
		$editurl_p = $script."?cmd=edit&page=".rawurlencode($_tbl_name);


		//元ページには「表」と「編集開始」ボタンを置く。
$string=<<<EOD
<div style='background-color:#eeeeee;'>
$_tbl_data

<input type="button" name="tableedit" value="$btext" onclick='tableedit_start("$_tbl_cashe");'/>
<input type="button" name="tableedit" value="$ptext" onclick='tableedit_pukiwikiedit("$editurl_p");'/>
</div>
EOD;
		//「編集開始」用のJS。最初の1回だけ書く。
		if($comment_no==0){
$string .=<<<EOD

<script>
function tableedit_start(tgtcache)
{

window.open(tgtcache, 'mywindow1', 'top='+(window.screenY)+', left='+(window.screenX)+',width='+(window.innerWidth-10)+', height='+(window.innerHeight-10)+', menubar=no, toolbar=no, scrollbars=yes');

}
function tableedit_pukiwikiedit(tgtcache)
{
	window.location.href = tgtcache;
}
</script>
EOD;
		}


	}
	

	return $string;
}


function plugin_tableedit_UpdateCache($tgt_table)
{
	// キャッシュファイルの更新。

	global $vars,$_btn_update;
	global $tgt_digest;

	// まず更新の必要性チェック。
	$isneed_cashe = false;
	$__tbl_path = DATA_DIR.encode($tgt_table).".txt";
	$__tbl_cashe = CACHE_DIR."tableedit_".encode($tgt_table).".html";

	if( !is_file($__tbl_cashe) ){
		//キャッシュがないので必ず作る。
		$isneed_cashe = true;	
	}else if(filemtime($__tbl_path)>=filemtime($__tbl_cashe)){
		// キャッシュよりも元ページのほうが新しい。キャッシュ更新。
		$isneed_cashe = true;	
	}
	// キャッシュ更新不要なら、何もせず戻る。
	if(!$isneed_cashe )return;


	// 以降、キャッシュ作成処理。
	$script = get_script_uri();
	$tgt_digest= md5(get_source($tgt_table, TRUE, TRUE));
	$__tbl_data = convert_html(file_get_contents($__tbl_path));


	// a-table が引き取れるように、表のHTMLを加工
	//<br>の整合。a-table が解釈できるのは<br>のみ。
	$__tbl_data=preg_replace( '/<br class="spacer" \/>/i','<br>',$__tbl_data);

	// リンクを取り出して pukiwiki 書式に。
	$regline ='/<a href="(.+?)"(.*?)>(.+?)<\/a>/i';
	$reg = preg_match($regline,$__tbl_data,$matches);

	while( $reg==1 ){  //  マッチしている 
		$reg2=preg_match('/title="(.+?)( \().*?"/',$matches[2],$matches2);
		if($reg2==1){
			$linkstr="[[$matches[3]>$matches2[1]]]";

		}else if( $matches[1]==$matches[3]){
			$linkstr = "[[$matches[1]]]";
		}else{
			$linkstr = "[[$matches[3]>$matches[1]]]";
		}
		$__tbl_data=preg_replace( $regline,$linkstr,$__tbl_data,1);

		$reg = preg_match($regline,$__tbl_data,$matches);

	}


	// 文字寄せと背景色を「クラス」部分に記述する
	$regline ='/<td class="style_td"([^>]*?)style="(.+?)">/i';
	$reg = preg_match($regline,$__tbl_data,$matches);

	while( $reg==1 ){  //  マッチしている 
		//上記で取り出したスタイルをチェック
		//text-align:(.+?); で検索、マッチすればそれを取り出す
		//background-color:(.+?); で検索、マッチすればそれを取り出す
		$_class = array();
		$regt = preg_match('/text-align:(.+);/i',$matches[2] ,$matchest);
		if($reg==1){
			array_push($_class, $matchest[1]);
					}
		$regt = preg_match('/background-color:(.+);/i',$matches[2] ,$matchest);
		if($reg==1){
			array_push($_class, $matchest[1]);	
		}

		if(count($_class)>0){
			//上記を " " でつなぐ→ $tmp_class に
			$tmp_class=implode(" ", $_class);
			$tmp_class=trim($tmp_class);
			//表データに対して replace 処理。style_td 部分を $tmp_class に置き換える
			$__tbl_data=preg_replace( $regline,'<TD class="'.$tmp_class.'"$1>',$__tbl_data,1);
		}

		//再度 表データを <td class=\"style_td\" style="(.+?)"> で検索
		$reg = preg_match($regline,$__tbl_data,$matches);
	}

	// 実際の編集用html作成
	$btn_end= PLUGIN_TABLEEDIT_BTN_UPDATE;
	$btn_reload= PLUGIN_TABLEEDIT_BTN_RELOAD;
	$btn_cancel= PLUGIN_TABLEEDIT_BTN_CANCEL;


	$string = <<<EOD
<!DOCTYPE html>
<html lang="ja">
<head>
	<title>Document</title>
  	<meta charset="UTF-8">
	<meta name=viewport content="width=device-width, initial-scale=1">
	<link rel='stylesheet' href="http://wikicreator.22web.org/53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plgfiles/js/a-table/fonts/a-table-icon.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css">
	<link rel="stylesheet" href="http://wikicreator.22web.org/53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plgfiles/js/a-table/css/a-table.css">
<style type="text/css">
.a-table td.red {
  background-color: red;
}

.a-table td.blue {
  background-color: blue;
}

.a-table td.yellow {
  background-color: yellow;
}
.a-table td.silver {
  background-color: silver;
}
.a-table td.lime {
  background-color: lime;
}
.a-table td.aqua {
  background-color: aqua;
}
.a-table td.violet {
  background-color: violet;
}

</style>
</head>
<body>
<div class='debug'></div>
<div class="main acms-admin-form" style='background-color:#eeeeee; padding: 5px;'>
<h1>編集：$tgt_table</h1>
		<div class="acms-admin-table-container">
			<div class="acms-admin-table-wrap">
			<div class="acms-admin-table-inner">
$__tbl_data
			</div>
			</div>
		</div>

<div class="column">
<p>pukiwiki text</p>
<form action="$script" method="post">
 <div>
  <input type="hidden" name="plugin" value="tableedit" />
  <input type="hidden" name="refer"  value="$tgt_table" />
  <input type="hidden" name="base" value="$tgt_table" />
  <input type="hidden" name="digest" value="$tgt_digest" />
	<div class="source-area">
  <textarea name="msg" class="result" rows="9" cols="70" > </textarea>
	<pre class="markdown"></pre>
	</div>
<input type="button" name="tableedita" value="$btn_reload" onclick='tableedit_reload();' style='margin-right:30px;' />
<input type="button" name="tableeditb" value="$btn_cancel" onclick='tableedit_cancel();' style='margin-right:30px;'/>
<input type="submit" name="tableedit" value="$btn_end" style='margin-right:30px;'/>
 </div>
</form>	
<script>
function tableedit_cancel()
{
	this.close();
}
function tableedit_reload()
{
	location.reload(true);
}
</script>

<hr>
	</div>
	<script
	src="https://code.jquery.com/jquery-2.2.4.min.js"
	integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44="
	crossorigin="anonymous"></script>
	<script src="http://wikicreator.22web.org/53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plgfiles/js/a-table/build/a-table.js"></script>
	<script>
		var table = new aTable('.style_table',{
			lang:'ja',
			mark:{
			  icon: {
				  td:false,
				  th:false
				}
			},
			selector:{
				option:[
					{label:'赤',value:'red'},
					{label:'黄色',value:'yellow'},
					{label:'グレー',value:'silver'},
					{label:'黄緑',value:'lime'},
					{label:'水色',value:'aqua'},
					{label:'紫',value:'violet'},
					{label:'青',value:'blue'},

				]
			},

		});
		table.afterRendered =
		table.afterEntered = function(){
			document.querySelector('.result').value = this.getPukiwikiSource();

		}
		table.afterRendered();
	</script>
<br />

EOD;

		file_put_contents ( $__tbl_cashe ,$string );

	return;

}


