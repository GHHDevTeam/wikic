<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: checkbox.inc.php,v 1.00 2004/11/07 07:00:36 ken Exp $
//

/////////////////////////////////////////////////
// コメントの名前テキストエリアのカラム数
define('COMMENT_NAME_COLS',15);
/////////////////////////////////////////////////
// コメントのテキストエリアのカラム数
define('COMMENT_COLS',70);
/////////////////////////////////////////////////
// コメントの挿入フォーマット
define('COMMENT_NAME_FORMAT','[[$name]]');
define('COMMENT_MSG_FORMAT','$msg');
define('COMMENT_NOW_FORMAT','&new{$now};');
/////////////////////////////////////////////////
// コメントの挿入フォーマット(コメント内容)
define('COMMENT_FORMAT',"\x08MSG\x08 -- \x08NAME\x08 \x08NOW\x08");
/////////////////////////////////////////////////
// コメントを挿入する位置 1:欄の前 0:欄の後
define('COMMENT_INS','1');
/////////////////////////////////////////////////
// コメントが投稿された場合、内容をメールで送る先
//define('COMMENT_MAIL',FALSE);

function plugin_checkbox_action()
{
	global $script,$vars,$post,$now;
	global $_title_updated,$_no_name;
	global $_msg_comment_collided,$_title_comment_collided;
	
	$post['msg'] = preg_replace("/\n/",'',$post['msg']);
	
	if ($post['msg'] == '')
	{
		return array('msg'=>'','body'=>'');
	}
	
	$head = '';
	if (preg_match('/^(-{1,2})(.*)/',$post['msg'],$match))
	{
		$head = $match[1];
		$post['msg'] = $match[2];
	}
	
	$_msg  = str_replace('$msg',$post['msg'],COMMENT_MSG_FORMAT);
	$_name = $post['name'] == '' ? $_no_name : $post['name'];
	$_name = ($_name == '') ? '' : str_replace('$name',$_name,COMMENT_NAME_FORMAT);
	$_now  = ($post['nodate'] == '1') ? '' : str_replace('$now',$now,COMMENT_NOW_FORMAT);
	
	$comment = str_replace("\x08MSG\x08", $_msg, COMMENT_FORMAT);
	$comment = str_replace("\x08NAME\x08",$_name,$comment);
	$comment = str_replace("\x08NOW\x08", $_now, $comment);
	$comment = $head.$comment;
	
	$postdata = '';
	$postdata_old  = get_source($post['refer']);
	$comment_no = 0;
	$comment_ins = ($post['above'] == '1');
	
// get checked options
	$chkdopt = $post['chk'];

	foreach ( $chkdopt as $key => $value ){
		$chkoption .= "$value :";
	}
	$comment = "$chkoption Comment $comment";

	foreach ($postdata_old as $line)
	{
		if (!$comment_ins)
		{
			$postdata .= $line;
		}
		if (preg_match('/^#checkbox/',$line) and $comment_no++ == $post['comment_no'])
		{
			$postdata = rtrim($postdata)."\n-$comment $tmp\n";
			if ($comment_ins)
			{
				$postdata .= "\n";
			}
		}
		if ($comment_ins)
		{
			$postdata .= $line;
		}
	}
	
	$title = $_title_updated;
	$body = '';
	if (md5(@join('',get_source($post['refer']))) != $post['digest'])
	{
		$title = $_title_comment_collided;
		$body = $_msg_comment_collided . make_pagelink($post['refer']);
	}
	
	page_write($post['refer'],$postdata);
	
	$retvars['msg'] = $title;
	$retvars['body'] = $body;
	
	$post['page'] = $vars['page'] = $post['refer'];
	
	return $retvars;
}
function plugin_checkbox_convert()
{
	global $script,$vars,$digest;
	global $_btn_comment,$_btn_name,$_msg_comment;
	static $numbers = array();
	
	if (!array_key_exists($vars['page'],$numbers))
	{
		$numbers[$vars['page']] = 0;
	}
	$comment_no = $numbers[$vars['page']]++;
	
// get options
	$options = func_num_args() ? func_get_args() : array();

	foreach ( $options as $value){
		$value = htmlspecialchars($value);
		$items .= "<input type=\"checkbox\" name=\"chk[]\" value=\"$value\">$value<br />";
	}


	$nametags = $_btn_name.'<input type="text" name="name" size="'.COMMENT_NAME_COLS."\" />\n";
// get options
//	$options = func_num_args() ? func_get_args() : array();
//	
//	if (in_array('noname',$options))
//	{
//		$nametags = $_msg_comment;
//	}
//	else
//	{
//		$nametags = $_btn_name.'<input type="text" name="name" size="'.COMMENT_NAME_COLS."\" />\n";
//	}
//	
//	$nodate = in_array('nodate',$options) ? '1' : '0';
//	$above = in_array('above',$options) ? '1' : (in_array('below',$options) ? '0' : COMMENT_INS);
	
	$s_page = htmlspecialchars($vars['page']);
	$comment_cols = COMMENT_COLS;
	$string = <<<EOD
<br />
<form action="$script" method="post">
 <div>
  <input type="hidden" name="comment_no" value="$comment_no" />
  <input type="hidden" name="refer" value="$s_page" />
  <input type="hidden" name="plugin" value="checkbox" />
  <input type="hidden" name="nodate" value="$nodate" />
  <input type="hidden" name="above" value="$above" />
  <input type="hidden" name="digest" value="$digest" />
  $items
  $nametags
  <input type="text" name="msg" size="$comment_cols" />
  <input type="submit" name="checkbox" value="$_btn_comment" />
 </div>
</form>
EOD;
	
	return $string;
}
?>
