<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: region.inc.php,v 1.2 2005/01/22 15:50:00 xxxxx Exp $
//

function plugin_region_convert()
{
	static $builder = 0;
	if( $builder==0 ) $builder = new RegionPluginHTMLBuilder();

	$builder->setDefaultSettings();

	if (func_num_args() >= 1){
		$args = func_get_args();
		$builder->setDescription( array_shift($args) );
		foreach( $args as $value ){
			if( preg_match("/^open/i", $value) ){
				$builder->setOpened();
			}elseif( preg_match("/^close/i", $value) ){
				$builder->setClosed();
			}
		}
	}
	return $builder->build();
}


class RegionPluginHTMLBuilder
{
	var $description;
	var $isopened;
	var $scriptVarName;
	var $callcount;

	function RegionPluginHTMLBuilder() {
		$this->callcount = 0;
		$this->setDefaultSettings();
	}
	function setDefaultSettings(){
		$this->description = "...";
		$this->isopened = false;
	}
	function setClosed(){ $this->isopened = false; }
	function setOpened(){ $this->isopened = true; }
	function setDescription($description){
		$this->description = convert_html($description);
		$this->description = preg_replace( "/^<p>/i", "", $this->description);
		$this->description = preg_replace( "/<\/p>$/i", "", $this->description);
	}
	function build(){
		$this->callcount++;
		$html = array();
		array_push( $html, $this->buildButtonHtml() );
		array_push( $html, $this->buildBracketHtml() );
		array_push( $html, $this->buildSummaryHtml() );
		array_push( $html, $this->buildContentHtml() );
		return join($html);
	}

	function buildButtonHtml(){
		$button = ($this->isopened) ? "-" : "+";
		return <<<EOD
<table cellpadding=1 cellspacing=2><tr>
<td valign=top>
	<span id=rgn_button$this->callcount style="cursor:pointer;font:normal 10px £Í£Ó £Ð¥´¥·¥Ã¥¯;border:gray 1px solid;"
	onclick="
	if(document.getElementById('rgn_summary$this->callcount').style.display!='none'){
		document.getElementById('rgn_summary$this->callcount').style.display='none';
		document.getElementById('rgn_content$this->callcount').style.display='block';
		document.getElementById('rgn_bracket$this->callcount').style.borderStyle='solid none solid solid';
		document.getElementById('rgn_button$this->callcount').innerHTML='-';
	}else{
		document.getElementById('rgn_summary$this->callcount').style.display='block';
		document.getElementById('rgn_content$this->callcount').style.display='none';
		document.getElementById('rgn_bracket$this->callcount').style.borderStyle='none';
		document.getElementById('rgn_button$this->callcount').innerHTML='+';
	}
	">$button</span>
</td>
EOD;
	}

	function buildBracketHtml(){
		$bracketstyle = ($this->isopened) ? "border-style: solid none solid solid;" : "border-style:none;";
		return <<<EOD
<td id=rgn_bracket$this->callcount style="font-size:1pt;border:gray 1px;$bracketstyle">&nbsp;</td>
EOD;
	}

	function buildSummaryHtml(){
		$summarystyle = ($this->isopened) ? "display:none;" : "display:block;";
		return <<<EOD
<td id=rgn_summary$this->callcount style="color:gray;border:gray 1px solid;$summarystyle">$this->description</td>
EOD;
	}

	function buildContentHtml(){
		$contentstyle = ($this->isopened) ? "display:block;" : "display:none;";
		return <<<EOD
<td valign=top id=rgn_content$this->callcount style="$contentstyle">
EOD;
	}

}// end class RegionPluginHTMLBuilder

?>
