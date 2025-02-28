<?php
// note plugin for PukiWiki
//   available under the GPL

define('NOTE_INDEX_FORMAT','*%d');
define('NOTE_FOOTER_FORMAT','*%d');
define('NOTE_FOOTER_ALIGN',TRUE);
define('NOTE_BIND_NO',TRUE);

function plugin_note_inline() {
	global $foot_explain;
	$footcnt_base = 10000;  // PukiWikiの脚注が$foot_explainで使う分を予約
	
	static $note_count = 0;  // 脚注数
	static $note_keywords = array();  // 脚注のキーワード
	static $note_ref = array();  // 他の注釈へのキーワード参照
	static $note_body = array();  // 注釈
	
	
	//パラメータの解釈
	if (func_num_args() != 1){
		$argstrs = func_get_args();
		return plugin_note_private_config($argstrs);
	}
	list($argstrs)=func_get_args();
	if (preg_match("/^([^:]*):*(.*)$/",$argstrs,$argstr)==0)
		return FALSE;
	$keyword=trim($argstr[1]);
	$note=rtrim($argstr[2]);
	if (($keyword=="")and($note==""))
		return FALSE;
	
	plugin_note_private_config($note_private_cfg);
	if ($note_count == 0)
		$note_count = $note_private_cfg['Count'];
	
	//脚注／注釈の追加
	$exists_key_no = array_search($keyword,$note_keywords);
	$is_exists_key = ((in_array($keyword,$note_keywords)) and ($keyword!="") );
	if (($is_exists_key) and ($note_private_cfg['Bind']))
	{
		$update_note_no = $exists_key_no;
		$update_notebody_no = $exists_key_no;
	}else{
		$update_note_no =++$note_count;
		$update_notebody_no = (($is_exists_key))? $exists_key_no : $update_note_no ;
		$note_ref[$update_note_no] = $update_notebody_no;
		$note_keywords[$update_note_no] = $keyword;
		$note_body[$update_note_no] = $note;
	}
	if (($note_body[$update_notebody_no]=="") and ($note != ""))
		$note_body[$update_notebody_no] = $note;
	
	//フッタの注釈用に同一キーワードの番号を一括りにする
	$footer_no_align = ($note_private_cfg['FooterAlign'])? "class=\"note_super\"" : "class=\"small\"" ;
	$footer_no_reflist = "";
	foreach($note_ref as $idx => $ref_no)
		if($ref_no==$update_notebody_no){
			$disp_no = sprintf($note_private_cfg['FooterFormat'],$idx);
			$footer_no_reflist .= <<<EOD
<a id="ex_notefoot_$idx" href="#ex_notetext_$idx" $footer_no_align>$disp_no&nbsp;</a>
EOD;
		}
	
	//フッタの注釈出力
	if($note_body[$update_notebody_no]!="")
		$foot_explain[$footcnt_base+$update_notebody_no] = <<<EOD
$footer_no_reflist
<span class="small">$note_body[$update_notebody_no]</span>
<br />
EOD;
	
	//本文の脚注出力
	$disp_no = sprintf($note_private_cfg['IndexFormat'],$update_note_no);
	$title="";
	if($note_body[$update_notebody_no]!=""){
		$title = htmlspecialchars(strip_htmltag($note_body[$update_notebody_no]));
		$title = "title=\"$title\"";
	}
	return "<a id=\"ex_notetext_$update_note_no\" href=\"#ex_notefoot_$update_note_no\" class=\"note_super\" $title>$disp_no</a>";
}

function plugin_note_private_config(&$args) {
	static $private_config = array(
		'ReadOnly' => FALSE,
		'IndexFormat' => NOTE_INDEX_FORMAT,
		'FooterFormat' => NOTE_FOOTER_FORMAT,
		'FooterAlign' => NOTE_FOOTER_ALIGN,
		'Bind' => NOTE_BIND_NO,
		'Count' => 0
	);

	if($private_config['ReadOnly']){
		// 設定された内容の読込
		$args = $private_config;
	}else{
		// 設定を行う
		$private_config['ReadOnly'] = TRUE;
		plugin_note_private_read_config($private_config);
		foreach($args as $idx => $arg){
			$arg = trim($arg);
			if ($arg == "")
				continue;
			switch($idx){
				case 0: // Bind:同一keywordを一つの番号に纏めるか
					$bindoption = array('bind'=>TRUE, 'loose'=>FALSE);
					if(array_key_exists($arg,$bindoption)){
						$private_config['Bind'] = $bindoption[$arg];
					}else{
						return FALSE;
					}
					break;
				case 1: // Count:付番開始位置
					if (ctype_digit($arg)){
						$count_num = (int) $arg;
						if (($count_num>0)and($count_num<10000))
							$private_config['Count'] = $count_num-1;
					}else
						return FALSE;
					break;
				default:
					return FALSE;
			}
		}
		$args = $private_config;
		return "";
	}
}
function plugin_note_private_read_config(&$args) {
	//configから設定の読み込み
	$cfg_sections = array(
		'Index-Format',
		'Footer-Format',
		'Footer-Align',
		'Bind-Note-No'
	);
	$cfg_format = array('*%d','(%d','%d)','(%d)','%d');
	$cfg_align = array('up' => TRUE,'normal' => FALSE);
	$cfg_bind = array('bind'=>TRUE, 'loose'=>FALSE);
	
	$config = new Config("plugin/note");
	$config->read();
	$cfgvalues = $config->get("Note-Config");
	foreach($cfg_sections as $section){
		list($cfgvalue) = $cfgvalues[$section];
		switch($section){
			case 'Index-Format':
				if(array_key_exists($cfgvalue,$cfg_format))
					$args['IndexFormat'] = $cfg_format[$cfgvalue];
				break;
			case 'Footer-Format':
				if(array_key_exists($cfgvalue,$cfg_format))
					$args['FooterFormat'] = $cfg_format[$cfgvalue];
				break;
			case 'Footer-Align':
				if(array_key_exists($cfgvalue,$cfg_align))
					$args['FooterAlign'] = $cfg_align[$cfgvalue];
				break;
			case 'Bind-Note-No':
				if(array_key_exists($cfgvalue,$cfg_bind))
					$args['Bind'] = $cfg_bind[$cfgvalue];
				break;
		}
	}

}

?>
