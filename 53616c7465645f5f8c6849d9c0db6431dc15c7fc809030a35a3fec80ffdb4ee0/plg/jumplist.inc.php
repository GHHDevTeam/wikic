<?php
//////////////////////////////////////////////////////////////////////
// jumplist.inc.php for PukiWiki available under the GPL
//       by teanan / Interfair Laboratory 2005-2006.
// ページリストから指定ページに移動するプラグイン

// [更新履歴]
// 2005-01-19 version 1.0 [初版]
// 2005-01-20 version 1.1
// ・相対指定したページが選択できないバグを修正。
// 2005-02-01 version 1.2
// ・エイリアス指定に対応(sagenさんのパッチを参考にしました)。
// 2006-09-16 version 1.3
// ・URL/InterWiki対応
// 2006-09-16 version 1.4
// ・不具合修正
// 2008-03-07 version 1.5
// ・リスト中に現在のページがあれば、それを選択状態にするように対応

function plugin_jumplist_action()
{
	global $script,$vars;

	$refer  = $vars['refer'];
	$select = $vars['select'];

	if(is_page($select)) {
		$s_page = rawurlencode($select);
		header("Location:$script?$s_page");
		exit;
	} else if(is_url($select)) {
		header("Location:$select");
		exit;
	}
	return array('msg' => '', 'body' => '');
}

function plugin_jumplist_convert()
{
	global $script,$vars,$BracketName;

	$refer = $vars['page'];
	$body = '';

	if(func_num_args() > 0) {
		$options = func_get_args();
		$_pattern =<<<EOD
(?:https?:\/\/[\w\/\@\$\(\)!?&%#:;.,~'=*+\-]+)| # URL only
\[\[                     # open bracket
(?:((?:(?!\]\]).)+)>)?   # (1) alias
(                        # (2) PageName,WikiName
 (?:$WikiName)|
 (?:$BracketName)|
 (?:https?:\/\/[\w\/\@\$\(\)!?&%#:;.,~'=*+\-]+)
)
(\#(?:[a-zA-Z][\w-]*)?)? # (3) anchor
\]\] |                   # close bracket
(                        # (4) InterWikiName
 (\[\[)?                       # (5)
  (?(5)(?:((?:(?!\]\]).)+)>)?) # (6) alias
  ((?:(?!\s|:|\]\]).)+):       # (7) name
  (.+)                         # (8) param
 (?(5)\]\])
)
EOD;

		$select_options = '';
		foreach($options as $name) {
			$_selected = '';
			$match = array();
			if(preg_match("/$_pattern/x",$name,$match)) {
				$r_value = '';
				$s_name  = '';
				if (is_url($match[0])) {
					// URLのみ
					$r_value = $match[0];
					$s_name  = htmlspecialchars($match[0]);
				} else if(isset($match[7])) {
					// InterWikiName
					$iname = $match[7];
					$param = $match[8];
					$url = get_interwiki_url($iname, $param);
					if (is_url($url)) {
						$_name = empty($match[6])? "$iname:$param" : $match[6];
						$r_value = $url;
						$s_name  = htmlspecialchars($_name);
					}
				} else {
					// BracketName
					$checkpage = get_fullname($match[2],$refer);
					// 存在するページのみをselectできるようにする
					if(is_page($checkpage) || is_url($checkpage)) {
						$r_value = $checkpage;
						$_name  = empty($match[1])? $match[2] : $match[1];
						$s_name = htmlspecialchars($_name);
						if(is_page($checkpage) && $checkpage === $refer) {
							$_selected = ' selected="selected"';
						}
					}
				}
				if(! empty($r_value)) {
					$select_options .= "<option value=\"$r_value\"$_selected>$s_name</option>\n";
				}
			}
		}
		if($select_options!='') {
			$s_refer = htmlspecialchars($refer);
			$body .= <<<EOD
<form action="$script" method="post">
 <div>
  <input type="hidden" name="cmd" value="jumplist" />
  <input type="hidden" name="refer" value="$s_refer" />
  <select name="select">
$select_options</select>
  <input type="submit" name="jump" value="GO" />
 </div>
</form>
EOD;
		}
	}
	return $body;
}
?>
