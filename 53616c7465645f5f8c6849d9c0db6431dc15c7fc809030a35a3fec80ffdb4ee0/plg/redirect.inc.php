<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// usage: 
// #redirect(WikiName)
//
// $Id$
//
// ver.1.02 外部リンクを復活(注釈つきで)、空白ありのページにも飛べるように
// ver.1.01 外部リンク機能を削除
// ver.1.00 新規公開
//

function plugin_redirect_convert()
{
	//***
	// $ALLOW_EXTERNAL_LINK
	// 外部へのリンクを許可する場合は 1 にします。
	// 
	//【警告】
	// 外部リンクを許可すると、悪意を持ったユーザの攻撃に
	// 悪用される場合があります。
	// (アダルトサイトに自動転送されるとか、ブラクラとして
	//  使用されるとか)
	// 
	// この機能を有効にするのは、すべてのWiki編集者が信頼
	// できることが確実な場合に限定してください。
	// (社内のみに公開するWikiとか、全ページ編集に権限を
	//  設定している場合とか)
	// 
	// この機能を有効にしたことにより発生した損害等について
	// 作者は一切の責任を負いません。
	//***
	
	$ALLOW_EXTERNAL_LINK = 0;
	
	
	
	//// 以下めいん
	global $vars;
	
	list($to, $sec, $page_name) = func_get_args();
	$to = htmlsc($to);
	$page_name = htmlsc($page_name);
	
	$sec = (($sec == null || $sec < 1 ) ? 5 : $sec) ;
	if ($to == null || !is_numeric($sec)) {
		$result = "使用方法: #redirect(ページ名,[待ち秒数],[ページ別名])";
		$result .= "($to / $sec / $page_name)";
	} else {
		$msec = $sec * 1000;
		
		//ex
		if($ALLOW_EXTERNAL_LINK == 1 && preg_match("/^http:|^https:/", $to)){ 
			$redirect_to = $to;
		//in
		} else {
			$redirect_to = "$script?" . rawurlencode($to);
		}
		if ($page_name == null) {
			$page_name = $to;
		}

	// 転送Javascript
	$result = <<<END_OF_SCRIPT
<strong>$sec</strong> 秒後に <strong><a href="$redirect_to">$page_name</a></strong> に移動します。<br />	
(移動しない場合は、上のリンクをクリックしてください。)<br />
<SCRIPT type="text/javascript">
<!--
setTimeout("link()", $msec);
function link(){
location.href='$redirect_to';
}
-->
	</SCRIPT>
END_OF_SCRIPT;
	}
	
	//
	return $result;
};
?>
