<?php
// $Id: tvote.inc.php,v 0.25 2005/02/12 18:00:00 r.tokiwa Exp $

# Note: based on vote.inc.php,v 1.14 2003/07/03 05:28:04 arino
# Copyright: Ryota Tokiwa
# License: GPL
# Uage: See README.TXT

define('_TVOTE_OPENCOUNT',TRUE);
define('_TVOTE_NOTIMESTAMP',TRUE);
define('_TVOTE_VOTEDLIST_MAX','30');
define('_TVOTE_VALIDDAYS',180);
define('_TVOTE_SORT',TRUE); 
define('_TVOTE_ADD',TRUE);
define('_TVOTE_FREEZE_SYNC',TRUE);
define('_TVOTE_MD5_CHECK',FALSE);

########################################
define('C_DELIM','-'); 

function plugin_tvote_action()
{
	global $post,$vars,$script,$cols,$rows;
	global $_title_collided,$_msg_collided,$_title_updated,$_title;
	global $_vote_plugin_choice, $_vote_plugin_votes;
#	 $vars['tvote_info_'.$tvote_no] .= "debug";
	$timestamp=_TVOTE_NOTIMESTAMP;
	$votedlist_max=_TVOTE_VOTEDLIST_MAX;
	$postdata_old  = get_source($post['refer']);
	$tvote_no = -1;
	$title = $body = $postdata = '';

	foreach($postdata_old as $line) {
		if (!preg_match("/^#tvote\((.*)\)\s*$/",$line,$arg)) {
			$postdata .= $line;
			continue;
		}
		if (++$tvote_no != $post['tvote_no']) {
			$postdata .= $line;
			continue;
		}
		$args = csv_explode(',', $arg[1]);
		$items = null;
		$tvoteflag=false;
		$_votedlist=explode(C_DELIM, $_COOKIE['tvote_'.$post['refer'].'_'.$post['tvote_no']]);

		foreach( $_votedlist as $key ) {
			if (preg_match("/^([\da-f]{8})$/i",$key,$match))
				$votedlist[$match[1]]=1;
		}
		foreach($args as $arg) {
			$cnt = 0;
			if (preg_match("/^(.+)\[([^\[]*)\]$/",$arg,$match)) {
				$arg = $match[1];
				$cnt = intval($match[2]);
				if (!is_int($cnt) || $cnt<0) $cnt=0;
			}
			$e_arg = encode($arg);
			if (!empty($post["tvote_$e_arg"]) and $post["tvote_$e_arg"] == $_vote_plugin_votes) {
				$hash=sprintf('%08x',crc32($arg));
				$tvoteflag=true;
				if (!$votedlist[$hash]) {
					if ($cnt<0x7FFFFFFF) $cnt++;
					$votedlist[$hash]=1;
				}
			}
			if(!empty($arg)) {
				$items[$arg] = array($cnt,$arg);
			}
		}
		if ((!empty($post['add_submit']) || !$tvoteflag) && !empty($post['tvote_add'])) {
			$add = htmlspecialchars($post['tvote_add']);
# $trans_tbl = array ('"' => '&quot;');
# $add = strtr($post['tvote_add'],$trans_tbl); 
			$hash=sprintf('%08x',crc32($add));
			if (is_null($items[$add])) {
				$items[$add] = array(1,$add);
				$votedlist[$hash]=1;
			} else {
				if (!$votedlist[$hash]) {
					if ($items[$add][0]<0x7FFFFFFF) $items[$add][0]++;
					$votedlist[$hash]=1;
				}
			}
		}
		if (count($votedlist)>$votedlist_max) array_shift($votedlist);
		$new_value=@join(C_DELIM,array_keys($votedlist));
		$_COOKIE['tvote_'.$post['refer'].'_'.$post['tvote_no']]=$new_value;
		if (_TVOTE_SORT) {
			$cmpfunc = create_function('$a, $b', 'return ($a[0]==$b[0]?strcasecmp($a[1],$b[1]):$b[0]-$a[0]);');
			uasort($items,$cmpfunc);
		}
		foreach ($items as $key => $value) {
			$votes[] = '"'.$key.'['.$value[0].']"';
		}
		$tvote_str = '#tvote('.@join(',',$votes).")\n";
		$postdata_input = $tvote_str;
		$postdata .= $tvote_str;
	}
	if (_TVOTE_MD5_CHECK && (md5(@join('',get_source($post['refer']))) != $post['digest'])) {
		$title = $_title_collided;
		$s_refer = htmlspecialchars($post['refer']);
		$s_digest = htmlspecialchars($post['digest']);
		$s_postdata_input = htmlspecialchars($postdata_input);
		$body = <<<EOD
$_msg_collided
<form action="$script?cmd=preview" method="post">
 <div>
  <input type="hidden" name="refer" value="$s_refer" />
  <input type="hidden" name="digest" value="$s_digest" />
  <textarea name="msg" rows="$rows" cols="$cols" id="textarea">$s_postdata_input</textarea><br />
 </div>
</form>
EOD;
	} else {
		page_write($post['refer'],$postdata,$timestamp);
		$title = $_title_updated;
	}
	$retvars['msg'] = $title;
	$retvars['body'] = $body;
	$post['page'] = $post['refer'];
	$vars['page'] = $post['refer'];
	return $retvars;
}

function plugin_tvote_convert()
{
	global $script,$vars,$digest;
	global $_vote_plugin_choice, $_vote_plugin_votes;
	static $numbers = array();
	$style = 'padding-left:0.5em;padding-right:0.5em';
//	 $vars['tvote_info_'.$tvote_no] .= "debug";

	if (empty($vars['page']))
		return '';
	if (!array_key_exists($vars['page'],$numbers))
		$numbers[$vars['page']] = 0;
	$tvote_no = $numbers[$vars['page']]++;
	$args = func_get_args();
	$s_page = htmlspecialchars($vars['page']);
	$s_digest = htmlspecialchars($digest);
	$_votedlist=explode(C_DELIM, $_COOKIE['tvote_'.$s_page.'_'.$tvote_no]);
	foreach( $_votedlist as $key ) {
		if (preg_match("/^([\da-f]{8})$/i",$key,$match))
			$votedlist[$match[1]]=1;
	}
	$view_count=_TVOTE_OPENCOUNT || ($votedlist) || (is_freeze($vars['page']) && _TVOTE_FREEZE_SYNC);
	if($view_count) {
		$votecount_head='得票数';
		$votepercent_head='得票率';
	}
	$body = <<<EOD
<a id="tvote$tvote_no"></a>
<form action="$script#tvote$tvote_no" method="post">
 <table cellspacing="0" cellpadding="2" class="style_table" summary="tvote">
  <tr>
   <td align="left" class="vote_label" style="padding-left:0.5em;padding-right:0.5em"><strong>$_vote_plugin_choice</strong>
    <input type="hidden" name="plugin" value="tvote" />
    <input type="hidden" name="refer" value="$s_page" />
    <input type="hidden" name="tvote_no" value="$tvote_no" />
    <input type="hidden" name="digest" value="$s_digest" />
   </td>
   <td align="right" class="vote_label"><strong>$votecount_head</strong></td>
   <td align="right" class="vote_label"><strong>$votepercent_head</strong></td>
   <td align="center" class="vote_label"><strong>
EOD;
	if(!(is_freeze($vars['page']) && _TVOTE_FREEZE_SYNC))
		$body .= $_vote_plugin_votes;
	$body .= '</strong></td></tr>';
	$tdcnt = 0;
	$pollcnt = 0;
	$itemlist=array();
	foreach($args as $arg) {
		$cnt = 0;
		if (preg_match("/^(.+)\[([^\[]*)\]$/",$arg,$match)) {
			$arg = $match[1];
			$cnt = intval($match[2]);
			if (!is_int($cnt) || $cnt<0) $cnt=0;
			$polltotal+=$cnt;
		}
		$itemlist[$arg]=$cnt;
	}
	foreach($itemlist as $key => $cnt) {
		$e_arg = encode($key);
#		$trans_tbl = array ('&quot;' => '"');
#		$item = strtr($key,$trans_tbl); 
		$trans_tbl = array_flip(get_html_translation_table(HTML_ENTITIES));
		$item = strtr($key, $trans_tbl);
		$html = make_link($item);
		$cntp=sprintf("%.1f",$cnt*100/$polltotal).'%';
		if(!$view_count) {
			unset($cnt);
			unset($cntp);
		}
		$cls = ($tdcnt++ % 2)  ? 'vote_td1' : 'vote_td2';
		$body .= <<<EOD
  <tr>
   <td align="left" class="$cls" style="$style">$html</td>
   <td align="right" class="$cls" style="$style">$cnt</td>
   <td align="right" class="$cls" style="$style">$cntp</td>
   <td align="right" class="$cls" style="$style">
EOD;
		$hash=sprintf('%08x',crc32($key));
		$itemshash[$hash] = 1;
		if(!(is_freeze($vars['page']) && _TVOTE_FREEZE_SYNC)) {
			if (!$votedlist[$hash])
				$body .= "<input type=\"submit\" name=\"tvote_$e_arg\" value=\"$_vote_plugin_votes\" class=\"submit\" />";
			else
				$body .= '投票済み';
		} //else	$body .= 'ÄùÀÚ';
		$body .= '</td></tr>';
	}
	if(!(is_freeze($vars['page']) && _TVOTE_FREEZE_SYNC) && _TVOTE_ADD) {
		$cls = ($tdcnt++ % 2)  ? 'vote_td1' : 'vote_td2';
		$body .= <<<EOD
  <tr>
   <td align="left" class="$cls" colspan="3" style="$style">その他
<input type="text" size="40" name="tvote_add" value="" />
</td>
   <td align="right" class="$cls" style="$style">
<input type="submit" name="add_submit" value="投票" class="submit"  />
   </td>
  </tr>
EOD;
	}
	if($view_count) {
		$cls = ($tdcnt++ % 2)  ? 'vote_td1' : 'vote_td2';
		$body .= <<<EOD
  <tr>
   <td align="left" class="$cls" style="$style">投票総数</td>
   <td align="right" class="$cls" style="$style">$polltotal</td>
   <td align="right" class="$cls" style="$style"></td>
   <td align="right" class="$cls" style="$style"></td>
  </tr>
EOD;
	}
	$body .= '</table></form>';
	foreach( array_keys($votedlist) as $key )
		if (!$itemshash[$key])
			unset($votedlist[$key]);
	$new_value=@join(C_DELIM,array_keys($votedlist));
	setcookie('tvote_'.$s_page.'_'.$tvote_no,$new_value,time()+3600*24*_TVOTE_VALIDDAYS);
	return $vars['tvote_info_'.$tvote_no].$body;
}
?>
