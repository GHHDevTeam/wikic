<?php
// PukiWiki - Yet another WikiWikiWeb clone
// articleplus.inc.php
//
 /*
 メッセージを変更したい場合はLANGUAGEファイルに下記の値を追加してからご使用ください
	$_btn_name    = 'お名前';
	$_btn_article = '記事の投稿';
	$_btn_subject = '題名: ';

 ※$_btn_nameはcommentplusプラグインで既に設定されている場合があります

 投稿内容の自動メール転送機能をご使用になりたい場合は
 -投稿内容のメール自動配信
 -投稿内容のメール自動配信先
 を設定の上、ご使用ください。

 */

define('PLUGIN_ARTICLEPLUS_COLS',	70); // テキストエリアのカラム数
define('PLUGIN_ARTICLEPLUS_ROWS',	 5); // テキストエリアの行数
define('PLUGIN_ARTICLEPLUS_NAME_COLS',	24); // 名前テキストエリアのカラム数
define('PLUGIN_ARTICLEPLUS_SUBJECT_COLS',	60); // 題名テキストエリアのカラム数
define('PLUGIN_ARTICLEPLUS_NAME_FORMAT',	'[[$name]]'); // 名前の挿入フォーマット
define('PLUGIN_ARTICLEPLUS_SUBJECT_FORMAT',	'**$subject'); // 題名の挿入フォーマット

define('PLUGIN_ARTICLEPLUS_INS',	0); // 挿入する位置 1:欄の前 0:欄の後
define('PLUGIN_ARTICLEPLUS_COMMENT',	1); // 書き込みの下に一行コメントを入れる 1:入れる 0:入れない
define('PLUGIN_ARTICLEPLUS_AUTO_BR',	1); // 改行を自動的変換 1:する 0:しない

define('PLUGIN_ARTICLEPLUS_MAIL_AUTO_SEND',	0); // 投稿内容のメール自動配信 1:する 0:しない
define('PLUGIN_ARTICLEPLUS_MAIL_FROM',	''); // 投稿内容のメール送信時の送信者メールアドレス
define('PLUGIN_ARTICLEPLUS_MAIL_SUBJECT_PREFIX', "[someone's PukiWiki]"); // 投稿内容のメール送信時の題名

// 投稿内容のメール自動配信先
global $_plugin_articleplus_mailto;
$_plugin_articleplus_mailto = array (
	''
);

function plugin_articleplus_action()
{
	global $post, $vars, $cols, $rows, $now;
	global $_title_collided, $_msg_collided, $_title_updated;
	global $_plugin_articleplus_mailto, $_no_subject, $_no_name;
	global $_msg_articleplus_mail_sender, $_msg_articleplus_mail_page;

	$script = get_base_uri();
	if (PKWK_READONLY) die_message('PKWK_READONLY prohibits editing');

	if ($post['msg'] == '')
		return array('msg'=>'','body'=>'');

	$name = ($post['name'] == '') ? $_no_name : $post['name'];
	$name = ($name == '') ? '' : str_replace('$name', $name, PLUGIN_ARTICLEPLUS_NAME_FORMAT);
	$subject = ($post['subject'] == '') ? $_no_subject : $post['subject'];
	$subject = ($subject == '') ? '' : str_replace('$subject', $subject, PLUGIN_ARTICLEPLUS_SUBJECT_FORMAT);
	$articleplus  = $subject . "\n" . '>' . $name . ' (' . $now . ')~' . "\n" . '~' . "\n";

	$msg = rtrim($post['msg']);
	if (PLUGIN_ARTICLEPLUS_AUTO_BR) {
		//改行の取り扱いはけっこう厄介。特にURLが絡んだときは…
		//コメント行、整形済み行には~をつけないように arino
		$msg = join("\n", preg_replace('/^(?!\/\/)(?!\s)(.*)$/', '$1~', explode("\n", $msg)));
	}
	$articleplus .= $msg . "\n\n" . '//';

	if (PLUGIN_ARTICLEPLUS_COMMENT) $articleplus .= "\n\n" . '#commentplus' . "\n";

	$postdata = '';
	$postdata_old  = get_source($post['refer']);
	$articleplus_no = 0;

	foreach($postdata_old as $line) {
		if (! PLUGIN_ARTICLEPLUS_INS) $postdata .= $line;
		if (preg_match('/^#articleplus/i', $line)) {
			if ($articleplus_no == $post['articleplus_no'] && $post['msg'] != '')
				$postdata .= $articleplus . "\n";
			++$articleplus_no;
		}
		if (PLUGIN_ARTICLEPLUS_INS) $postdata .= $line;
	}

	$postdata_input = $articleplus . "\n";
	$body = '';

	if (md5(get_source($post['refer'], TRUE, TRUE)) !== $post['digest']) {
		$title = $_title_collided;

		$body = $_msg_collided . "\n";

		$s_refer    = htmlsc($post['refer']);
		$s_digest   = htmlsc($post['digest']);
		$s_postdata = htmlsc($postdata_input);
		$body .= <<<EOD
<form action="$script?cmd=preview" method="post">
 <div>
  <input type="hidden" name="refer" value="$s_refer" />
  <input type="hidden" name="digest" value="$s_digest" />
  <textarea name="msg" rows="$rows" cols="$cols" id="textarea">$s_postdata</textarea><br />
 </div>
</form>
EOD;

	} else {
		page_write($post['refer'], trim($postdata));

		// 投稿内容のメール自動送信
		if (PLUGIN_ARTICLEPLUS_MAIL_AUTO_SEND) {
			$mailaddress = implode(',', $_plugin_articleplus_mailto);
			$mailsubject = PLUGIN_ARTICLEPLUS_MAIL_SUBJECT_PREFIX . ' ' . str_replace('**', '', $subject);
			if ($post['name'])
				$mailsubject .= '/' . $post['name'];
			$mailsubject = mb_encode_mimeheader($mailsubject);

			$mailbody = $post['msg'];
			$mailbody .= "\n\n" . '---' . "\n";
			$mailbody .= $_msg_articleplus_mail_sender . $post['name'] . ' (' . $now . ')' . "\n";
			$mailbody .= $_msg_articleplus_mail_page . $post['refer'] . "\n";
			$mailbody .= '   URL: ' . get_page_uri($post['refer'], PKWK_URI_ABSOLUTE) . "\n";
			$mailbody = mb_convert_encoding($mailbody, 'JIS');

			$mailaddheader = 'From: ' . PLUGIN_ARTICLEPLUS_MAIL_FROM;

			mail($mailaddress, $mailsubject, $mailbody, $mailaddheader);
		}

		$title = $_title_updated;
	}
	$retvars['msg'] = $title;
	$retvars['body'] = $body;

	$post['page'] = $post['refer'];
	$vars['page'] = $post['refer'];

	return $retvars;
}

function plugin_articleplus_convert()
{
	global $vars, $digest;
	global $_btn_article, $_btn_name, $_btn_subject;
	static $numbers = array();

	$script = get_base_uri();
	if (PKWK_READONLY) return ''; // Show nothing

	if (! isset($numbers[$vars['page']])) $numbers[$vars['page']] = 0;

	$articleplus_no = $numbers[$vars['page']]++;

	$s_page       = htmlsc($vars['page']);
	$s_digest     = htmlsc($digest);
	$name_cols    = PLUGIN_ARTICLEPLUS_NAME_COLS;
	$subject_cols = PLUGIN_ARTICLEPLUS_SUBJECT_COLS;
	$articleplus_rows = PLUGIN_ARTICLEPLUS_ROWS;
	$articleplus_cols = PLUGIN_ARTICLEPLUS_COLS;
	$string = <<<EOD
<script src="https&#58;//ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<form action="$script" method="post" class="_p_articleplus_form">
 <div>
  <input type="hidden" name="articleplus_no" value="$articleplus_no" />
  <input type="hidden" name="plugin" value="articleplus" />
  <input type="hidden" name="digest" value="$s_digest" />
  <input type="hidden" name="refer" value="$s_page" />
  <label for="_p_articleplus_name_$articleplus_no">$_btn_name</label>
  <input type="text" name="name" id="_p_articleplus_name_$articleplus_no" size="$name_cols" /><br />
  <label for="_p_articleplus_subject_$articleplus_no">$_btn_subject</label>
  <input type="text" name="subject" id="_p_articleplus_subject_$articleplus_no" size="$subject_cols" /><br />
  <textarea id="articleplus" name="msg" rows="$articleplus_rows" cols="$articleplus_cols">\n</textarea><br />
  <input type="submit" name="articleplus" value="$_btn_article" />
 </div>
</form>
<script>
document.activeElement.onInput = function() 
{
    if ((document.activeElement.type == "text")||(document.activeElement.id == "articleplus")){
        dae = document.activeElement;
        text_len = dae.value.length;
        text_pos = dae.selectionStart;
        text_bf = dae.value.substr(0, text_pos);
        text_af = dae.value.substr(text_pos, text_len);
    }
};
document.activeElement.onclick = function() 
{
    if ((document.activeElement.type == "text")||(document.activeElement.id == "articleplus")){
        dae = document.activeElement;
        text_len = dae.value.length;
        text_pos = dae.selectionStart;
        text_bf = dae.value.substr(0, text_pos);
        text_af = dae.value.substr(text_pos, text_len);
    }
};
function inputToArticleplusArea(input_text) {
    dae.value = text_bf + input_text + text_af;
    text_len = dae.value.length;
    text_pos = text_pos + input_text.length;
    text_bf = dae.value.substr(0, text_pos);
    text_af = dae.value.substr(text_pos, text_len);
};
function inputToArticleplusArea2(input_text1,input_text2,input_text3) {
    size = window.prompt("サイズ:", "10");
    text1 = window.prompt("テキスト:", dae.value.substring(dae.selectionStart,dae.selectionEnd));
    text_af = dae.value.substr(text_pos + dae.value.substring(dae.selectionStart,dae.selectionEnd).length, text_len);
    dae.value = text_bf + input_text1 + size + input_text2 + text1 + input_text3 + text_af;
    text_len = dae.value.length;
    text_pos = text_pos + input_text.length;
    text_bf = dae.value.substr(0, text_pos);
    text_af = dae.value.substr(text_pos, text_len);
};
function inputToArticleplusAreaURL(input_text1,input_text2,input_text3) {
    text1 = window.prompt("テキスト:", "");
    url1 = window.prompt("URL:", "http://");
    dae.value = text_bf + "[["+text1+">"+url1+"]]" + text_af;
    text_len = dae.value.length;
    text_pos = text_pos + input_text.length;
    text_bf = dae.value.substr(0, text_pos);
    text_af = dae.value.substr(text_pos, text_len);
};
function inputToArticleplusArea3(input_text) {
    text_af = dae.value.substr(text_pos + dae.value.substring(dae.selectionStart,dae.selectionEnd).length, text_len);
    dae.value = text_bf + input_text + dae.value.substring(dae.selectionStart,dae.selectionEnd) + input_text + text_af;
    text_se_1 = dae.selectionEnd;
    text_len = dae.value.length;
    text_pos = text_pos + input_text.length;
    text_bf = dae.value.substr(0, text_pos);
    text_af = dae.value.substr(text_pos, text_len);
};
</script>
<a href="javascript:inputToArticleplusAreaURL()">[URL]</a>&nbsp;
<a href="javascript:inputToArticleplusArea3('\&#39;\&#39;')">[B]</a>&nbsp;
<a href="javascript:inputToArticleplusArea3('\&#39;\&#39;\&#39;')">[I]</a>&nbsp;
<a href="javascript:inputToArticleplusArea3('%%%')">[U]</a>&nbsp;
<a href="javascript:inputToArticleplusArea3('%%')">[S]</a>&nbsp;
<a href="javascript:inputToArticleplusArea2('&size(','){','};')">[サイズ]</a>&nbsp;
<a href="javascript:inputToArticleplusArea('&attachref();')">[添付]</a>&nbsp;
<a href="javascript:inputToArticleplusArea('&br;')">[改行]</a>&nbsp;
<a href="javascript:inputToArticleplusArea('&smile&#59;')"><img src="./image/face/smile.png"/></a>&nbsp;
<a href="javascript:inputToArticleplusArea('&bigsmile&#59;')"><img src="./image/face/bigsmile.png"/></a>&nbsp;
<a href="javascript:inputToArticleplusArea('&huh&#59;')"><img src="./image/face/huh.png"/></a>&nbsp;
<a href="javascript:inputToArticleplusArea('&oh&#59;')"><img src="./image/face/oh.png"/></a>&nbsp;
<a href="javascript:inputToArticleplusArea('&wink&#59;')"><img src="./image/face/wink.png"/></a>&nbsp;
<a href="javascript:inputToArticleplusArea('&sad&#59;')"><img src="./image/face/sad.png"/></a>&nbsp;
<a href="javascript:inputToArticleplusArea('&heart&#59;')"><img src="./image/face/heart.png"/></a>&nbsp;
EOD;
	return $string;
}
