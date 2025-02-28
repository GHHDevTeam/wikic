<?php
// $Id: gotaku.inc.php,v 0.5 2004/09/18 13:50:10 sha Exp $

/*
*プラグイン gotaku
 アンケートとか試験とかで使える。

*Usage
#gotaku(tracker_configname,[disp])

*引数
  最初の引数は、trackerのconfig名, 
  dispで得点表示。
*/

//=====================================================================
function plugin_gotaku_init()
{
	$messages = array(
	  '_gotaku_messages' => array(
		 'btn_submit' => '回答',
		 'name_score' => '[配点 $1点]',
		 'name_getpoint' => '[得点 $1点]',
		 'title_name' => '氏名：',
		 'title_post' => 'ご回答ありがとうございました',
		 'title_exist_error' => '既に登録されております',
		 'msg_exist_error' => '*あなたのIPアドレスが既に登録されていたため、記録しませんでした',
      ),
	);
	set_plugin_messages($messages);
}
//=====================================================================
function plugin_gotaku_convert()
{
	global $vars;
	$page = $vars['page'];
	
	$config_name = 'default';
	$args = func_get_args();
	if ( count($args) == 0 ) return "<p>no option of config_name</p>";

	$disp_flag = 0;
	$config_name = '';
	foreach ( $args as $opt ){
		if ( $opt == 'disp' ) {
			$disp_flag = 1;
		}
		else if ( $config_name == '' ) {
			$config_name = $opt;
		}
	}

	$config = new Config('plugin/gotaku/'.$config_name);
	if (!$config->read()){
		return "<p>config file '".htmlspecialchars($config_name)."' not found.</p>";
	}
	$config->config_name = $config_name;
	$params = new Parameters($config);
	$separator = $params->getValue('_separator');
	$separator = preg_replace('/<\/?p>/','', convert_html($separator));

	if ( $disp_flag == 1 ) return plugin_gotaku_disp($params);
	return plugin_gotaku_questions($config, $separator);
}
//=====================================================================
function plugin_gotaku_disp($params)
{
	$debug = '';
	$items = array();
	foreach ( $params->data as $item=>$ary ){
		$opts = $ary['option'];
		$debug .= "/opts[$item]=$opts";
		foreach ( explode(',',$opts) as $opt ){
			if ( $opt == 'list' ) {
				$items[$item] = 1;
				$debug .= "/item=$item";
			}
		}
	}

	$ary = plugin_gotaku_tablehead($params->config->page .'/list');
	if ( $ary === FALSE ) return FALSE;
	list($order,$index,$reg,$tablehead) = $ary;

	$ord = array();
	foreach ( $order as $item ){
		if ( array_key_exists($item, $items) ) $ord[] = $item;
	}

	$headreg = str_replace('$','',$reg);
	$debug .= $headreg;
	$outs = array();
	$itemskeys = array_merge($params->regitems,$params->regvalues);
	foreach ( $tablehead as $line ){
		if ( substr($line,0,1) != '|' ) continue;
		$tail = preg_replace($headreg,'',$line);
		if ( preg_match($headreg,$line,$match) ){
			$out = array();
			foreach ( $ord as $item ){
				$out[] = $match[$index[$item]];
			}
			$line = '|' . join('|',$out) . '|' . $tail;
			foreach ( $itemskeys as $item=>$val ){
				$line = str_replace($item, $val, $line);
			}
		}
		$outs[] = trim($line);
	}

	$logpage = $params->config->page . '/log';
	list($sorted,$content,$ips,$dbg) 
			= plugin_gotaku_readlog($logpage,$index,$reg);

	foreach ( $content as $a ){
		$out = array();
		foreach ( $ord as $item ){
			$out[] = $a[$index[$item]];
		}
		$outs[] = '|' . join('|',$out) . '|';
	}
//	$ret = join("\n",$outs) . $debug;
	$ret = join("\n",$outs);

//	return $ret;
	return convert_html($ret);
}
//=====================================================================
function plugin_gotaku_questions($config, $separator)
{
	global $script,$vars,$_gotaku_messages;

	$ary = plugin_gotaku_get_questions($config->page.'/sheet', $separator);
	$retval = join('',$ary);

	$page = $vars['page'];
	$s_title = htmlspecialchars($_gotaku_messages['btn_submit']);
	$s_page = htmlspecialchars($page);
	$s_config = htmlspecialchars($config->config_name);
	$retval .=<<<EOD
<input type="hidden" name="plugin" value="gotaku" />
<input type="hidden" name="refer"  value="$s_page" />
<input type="hidden" name="config" value="$s_config" />
EOD;
		return <<<EOD
<form enctype="multipart/form-data" action="$script" method="post">
<div>
$retval
<div>
{$_gotaku_messages['title_name']}<input type="text" size="20" name="name" />
<input type="submit" value="$s_title" />
</div>
</div>
</form>
EOD;
}
//=====================================================================
function plugin_gotaku_action()
{
	global $script,$vars,$post,$_gotaku_messages;

	$config_name = array_key_exists('config',$post) ? $post['config'] : '';
	$config = new Config('plugin/gotaku/'.$config_name);
	if (!$config->read()){
		return "<p>config file '".htmlspecialchars($config_name)."' not found.</p>";
	}
	$refer = array_key_exists('refer', $vars) ? $vars['refer'] : '';

	$config->config_name = $config_name;
	$params = new Parameters($config);
	$params->setValue('_refer', $refer);
	if ( array_key_exists('name', $vars) and $vars['name'] != '' ) {
		$params->setValue('_name', $vars['name']);
	}
	$answer = plugin_gotaku_marking($params);
	$postdata = convert_html($answer);
	$ret = plugin_gotaku_write($params);
	if ( $ret === FALSE ){
		$retvars['msg']  = $_gotaku_messages['title_exist_error'];
		$postdata = convert_html($_gotaku_messages['msg_exist_error'] . "\n")
				. $postdata;
	}
	else {
		$retvars['msg']  = $_gotaku_messages['title_post'];
	}

	$retvars['body'] = $postdata;
//	$retvars['body'] .= '<br />' . convert_html($ret);

	return $retvars;
}
//=====================================================================
function plugin_gotaku_tablehead($table)
{
	if ( ! is_page($table) ) return FALSE;
	$tablesrc = plugin_gotaku_get_source($table);
	$lastline =  '';
	$tablehead = array();
	$skip_flag = 0;
	foreach ( $tablesrc as $line ){
		$tch = substr($line,0,1);
		if ( $skip_flag == 0 ){
			if ( $tch == '|' ) 	$skip_flag = 1;
			$tablehead[] = $line;
			continue;
		}
		else if ( $skip_flag == 1 ){
			if ( $tch == '|' ) {
				if ( ! preg_match('/\|$/',$line,$match) ) $tablehead[] = $line;
				$lastline = $line;
				continue;
			}
		}
		break;
	}
	preg_match_all('/\[(\w+)\]/',$lastline,$matches);
	$order = $matches[1];
	$i = 1;
	foreach ( $order as $key ){
		$index[$key] = $i ++;
	}
	$reg = preg_replace('/\[\w+\]/','(.*)',$lastline);
	$reg = '/^' . str_replace('|', '\|', trim($reg)) . '$/';
	return array($order,$index,$reg,$tablehead);
}
//=====================================================================
function plugin_gotaku_readlog($logpage,$index,$reg)
{
	$ips = array();
	$sorted  = array();
	$content = array();
	$debug = '';
	if ( is_page($logpage) ) {
		$lines = plugin_gotaku_get_source($logpage);
		foreach ( $lines as $line ) {
			if ( preg_match($reg,trim($line),$match) ){
				// $sorted[]と$content[]は同期が必要。同時に代入すること。
				$sorted[]  = $index['_score'] > 0 ? $match[$index['_score']] : '';
				$content[] = $match;
				if ( $index['_ip'] > 0 ){
					$ips[$match[$index['_ip']]] = $match[$index['_name']];
				}
			}
		}
	}
	return array($sorted, $content, $ips, $debug);
}
//=====================================================================
class Parameters 
{
	var $data;
	var $regitems;
	var $regvalues;
	var $config;

	function Parameters($config){
		$this->config = $config;
		// 0=>項目名 1=>見出し 2=>形式 3=>オプション 4=>デフォルト値
		$this->regitems  = array();
		$this->regvalues = array();
		$this->data      = array();
		foreach ( $config->get('parameters') as $key ){
			$val = $key[4];
			if ( $key[2] == 'boolean' ) 
					$val = ( $val == 'TRUE' or $val == 'true' or $val == 1 );
			$this->data[$key[0]] = array(
				'title' => $key[1],
				'type'  => $key[2],
				'option' => $key[3],
				'value'  => $val,
			);
			if ( $key[2] == 'item' ) {
				$title = '~' . $key[1];
				$this->regitems[$this->mkTitleTag($key[0])]  = $title;
				$this->regvalues[$this->mkValueTag($key[0])] = $val;
			}
		}
	}
	function mkTitleTag($item){
		return '~[' . $item . ']';
	}
	function mkValueTag($item){
		return '[' . $item . ']';
	}
	function getTitle($item){
		return $this->data[$item]['title'];
	}
	function getType($item){
		return $this->data[$item]['type'];
	}
	function getOption($item){
		return $this->data[$item]['option'];
	}
	function getValue($item){
		return $this->data[$item]['value'];
	}
	function setValue($item,$val){
		$this->data[$item]['value'] = $val;
		$this->regvalues[$this->mkValueTag($item)] = $val;
	}
}
//=====================================================================
function plugin_gotaku_write($params)
{
	global $_SERVER;

	$debug = '/debug=';
	$ary = plugin_gotaku_tablehead($params->config->page .'/list');
	if ( $ary === FALSE ) return FALSE;
	list($order,$index,$reg,$tablehead) = $ary;

	$debug .= "order=".join('/',$order) .'/';
	$debug .= "index=".join('/',$index) .'/';
	$debug .= "reg=$reg/";

	$ips = array();
	$sorted  = array();
	$content = array();
	$logpage = $params->config->page . '/log';
	list($sorted,$content,$ips,$dbg) 
		= plugin_gotaku_readlog($logpage,$index,$reg);
//	$debug .= $dbg . '/' . join('/',$sorted) . '---' . join('/',array_keys($ips));
//	$debug .= $tablehead;

	if ( $params->getValue('_ipcheck') and array_key_exists($_SERVER['REMOTE_ADDR'], $ips) ){
		return FALSE;
	}

	$params->setValue('_ip', $_SERVER['REMOTE_ADDR']);
	$params->setValue('_date', 'now?');
	$params->setValue('_rank', -1);

	$newary = array('dummy');
	foreach ( $order as $tag ){
		$newary[] = $params->getValue($tag);
	}
	$content[] = $newary;
	$sorted[]  = $params->getValue('_score');
	if ( count($sorted)>1 ) arsort($sorted,SORT_NUMERIC);

	$ct = 0;
	$keys = array();
	foreach ( $newary as $val ){
		$tag = '\[' . $order[$ct++] . '\]';
		$keys[$tag] = $val;
	}
	$itemskeys = array_merge($params->regitems,$keys);

	$outs = array();
	foreach ( $tablehead as $line ){
		if ( substr($line,0,1) != '|' ) continue;
		foreach ( $itemskeys as $item=>$val ){
			$line = str_replace($item, $val, $line);
		}
		$outs[] = trim($line);
	}
	$no = 0;
	$ct = 0;
	$prescore = -1;
	foreach ( $sorted as $num=>$sc ){
		if ( $prescore == $sc ) {
			$ct ++;
		}
		else {
			$no += $ct>0 ? $ct+1 : 1;
			$ct = 0;
		}
		$match = $content[$num];
		$match[$index['_rank']] = $no;
		array_shift($match);
		$str = '|' . join('|',$match) . '|';
		$line = make_str_rules($str);
		$outs[]= $line;
		$prescore = $sc;
	}
	if ( 0 ) {
//		$outs[] = "score=$score/name=$name/num=$num/logpage=$logpage";
//		$outs[] = $debug;
//		$outs[] = "#printenv";
		$ary = array_pad($outs, 0, 0);
		foreach ( $ary as $key=>$line ){
			$outs[] = ' ' . $key .' / '. $line;
		}
	}
	$postdata = join("\n",$outs);
	// 書き込み
	page_write($logpage,$postdata);
	return $postdata;
}
//=====================================================================
function plugin_gotaku_get_questions($config_page, $separator)
{
	global $_gotaku_messages;

	if ( ! is_page($config_page) )	return array();
	$srcs = plugin_gotaku_get_source($config_page);
	$outs = array();
	$qct = 0;
	$qt = array();
	$separator = $separator ? $separator : ' / ';
	foreach ( $srcs as $line ){
		$html = '';
		if ( preg_match('/^([\*\-]{1,3}\s*)(.+)$/',$line,$match) ){
			$html = '';
			if ( count($qt) ) $html = join($separator, $qt);
//			$str =  str_replace('$1',$match[2], $_gotaku_messages['name_score']);
//			$html .= convert_html( $match[1] . $str . $match[3]);
			$html .= convert_html( $match[0]);
			$qct ++;
			$act = 0;
			$qt = array();
		}
		else if ( preg_match('/^(\+{1,3}\s*)\[[^\d]*(\d+)[^\]]*\]\s*(.+)\[[^\]]*\]\s*$/',$line,$match) || preg_match('/^(\+{1,3}\s*)\[[^\d]*(\d+)[^\]]*\]\s*(.+)$/',$line,$match) ){
			$qt[$act] =<<<EOD
<input type="radio" name="qct$qct" value="ans$act" /> $match[3]
EOD;
			$act ++;
			continue;
		}
		else if ( preg_match('/^(\*{1,3}|\-{1,3}|\+{1,3})/',$line,$match) ){
			continue;
		}
		else {
			$html = '';
			if ( count($qt) ) $html = '<div>' . join($separator, $qt) . "\n</div><br />\n";
			$html .= convert_html($line);
			$act = 0;
			$qt = array();
		}
		$outs[] = $html;
	}
	if ( count($qt) ) $outs[] = join($separator, $qt);
	return $outs;
}
//=====================================================================
function plugin_gotaku_marking(&$params)
{
	global $vars, $_gotaku_messages;

	$config_page = $params->config->page . '/sheet';
	if ( ! is_page($config_page) )	return array();
	$srcs = plugin_gotaku_get_source($config_page);
	$outs = array();
	$qct = $total = $score = $max = $linenum = 0;
	$title   = '';
	foreach ( $srcs as $line ){
		if ( preg_match('/^([\*\-]{1,3}\s*)(.+)$/',$line,$match) ){
			$title = $line;
			$total += $max;
			$max = 0;
			$qct ++;
			$act = 0;
			$linenum = count($outs);
		}
		else if ( preg_match('/^(\+{1,3}\s*)\[[^\d]*(\d+)[^\]]*\]\s*(.+)$/',$line,$match) ){
			$max = $match[2] > $max ? $match[2] : $max;
			if ( $vars["qct$qct"] == "ans$act" ){
				$score += $match[2];
				if ( preg_match('/^([\*\-]{1,3}\s*)(.+)$/',$title,$mat) ){
					$title = $mat[1] . $_gotaku_messages['name_getpoint'] . $mat[2];
				}
				$outs[$linenum] = str_replace('$1', $match[2], $title);
				$line = $match[1] . "&color(red)\{{$_gotaku_messages['name_score']} ★$match[3]};";
			}
			else {
				$line = $match[1] . "{$_gotaku_messages['name_score']} $match[3]";
			}
			$line = str_replace('$1',$match[2],$line);
			$act ++;
		}
		else if ( preg_match('/^(\*{1,3}|\-{1,3}|\+{1,3})/',$line,$match) ){
			continue;
		}
		else {
			$total += $max>0 ? $max : 0;
			$max = $act = 0;
		}
		$outs[] = $line;
	}
	$total += $max>0 ? $max : 0;

	$params->setValue('_score', $score);
	$params->setValue('_total', $total);
	if ( $total > 0 ) $percent = round($score/$total*100,2);
	else              $percent = 0;
	$params->setValue('_percent', $percent);

	$config_output = $params->config->page ."/result";
	if ( ! is_page($config_output) )	return array($score,$total);

	$itemskeys = array_merge($params->regitems,$params->regvalues);
	$srcs = plugin_gotaku_get_source($config_output);
	$skip_flag = 0;
	$ary = array();
	foreach ( $srcs as $line ){
		foreach ( $itemskeys as $item=>$val ){
			$line = str_replace($item, $val, $line);
		}
		$ary[] = $line;
	}
	if ( $params->getValue('_marking') ) return array_merge($ary,$outs);
	return $ary;
}
//=====================================================================
// tracker.inc.phpからコピー
function plugin_gotaku_get_source($page)
{
	$source = get_source($page);
	// 見出しの固有ID部を削除
	$source = preg_replace('/^(\*{1,3}.*)\[#[A-Za-z][\w-]+\](.*)$/m','$1$2',$source);
	// #freezeを削除
	return preg_replace('/^#freeze\s*$/m','',$source);
}
?>
