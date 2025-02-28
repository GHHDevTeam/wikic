<?php
/////////////////////////////////////////////////
// License: The same as PukiWiki
// PukiWiki - Yet another WikiWikiWeb clone.
// $Id: infobox.inc.php,v 0.00 2013/07/06 00:00:00 Kazuna Asato Exp $
//

define('PLUGIN_INFOBOX_FORMAT',':config/plugin/infobox/format');
function plugin_infobox_convert()
{
  global $script,$vars,$head_tags;
  static $taged = false;
  
  if(!$taged){
      $head_tags[] = <<<EOD
      <style>
      .infobox{
          float:right;
          clear:right;
      }
      </style>
      EOD;
      $taged = true;
  }
  
  $r='';
  $CRLF=array("\r","\n");

  $args = func_get_args();
  $prm_tbl=array_pop($args);

  $flg=FALSE;
  foreach($CRLF as $cr){
    if (strpos($prm_tbl,$cr)!==FALSE){$flg=TRUE; break;}
  }
  if ($flg===FALSE){ return convert_html('infobox:parameter-error');}
  $fmt_page=(count($args)>0)?$args[0]:PLUGIN_INFOBOX_FORMAT;
  if (!is_page($fmt_page,TRUE)){ return convert_html('infobox:no-such-page:[['.$fmt_page.']]'); }
  $fmt=get_source($fmt_page,TRUE,FALSE);

  $prm=preg_split("/[\r\n]/",$prm_tbl);
  foreach($prm as $v){
    if (strlen($v)<1){continue;}
    $vv=explode("=",$v);
    $vvkey='['.$vv[0].']';
    $vvval=$vv[1];
    $fmt=str_replace ( $vvkey , $vvval , $fmt );
  }

  $r='<div class="infobox">'.convert_html($fmt).'</div>';

  return $r;
}
?>