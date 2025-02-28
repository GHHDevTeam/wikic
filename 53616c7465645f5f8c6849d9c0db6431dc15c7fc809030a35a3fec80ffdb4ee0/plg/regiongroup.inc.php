<?php
/////////////////////////////////////////////////
// PukiWiki - Yet another WikiWikiWeb clone.
//
// $Id: regiongroup.inc.php,v 1.0 2008  Tomose
// Based on region.inc.php,v 1.2 2005/01/22 15:50:00 xxxxx Exp $


function plugin_regiongroup_convert()
{
	static $builder = 0;
	if( $builder==0 ) $builder = new RegiongroupPluginHTMLBuilder();

	$builder->setDefaultSettings();

	return $builder->build();
}


class RegiongroupPluginHTMLBuilder
{
	var $description;
	var $isopened;
	var $scriptVarName;
	var $callcount;

	function RegiongroupPluginHTMLBuilder() {
		$this->callcount = 0;
		$this->setDefaultSettings();
	}
	function setDefaultSettings(){
		$this->description = "...";
		$this->isopened = false;
	}

	function setClosed(){ $this->isopened = false; }
	function setOpened(){ $this->isopened = true; }

	function setDescription($description){ $this->description = "aaa"; }

	function build(){
		if( $this->callcount == 0 ) {
			$this->callcount++;
			
			$html = array();
			array_push( $html, $this->buildButtonHtml() );

			return join($html);
		}
	}

	function buildButtonHtml(){
		$button = "Open all regions.";
		return <<<EOD

<table cellpadding=1 cellspacing=2><tr>
<td valign=top>
<!--
-->
	<span id=rgn_opener style="cursor:pointer;border:gray 1px solid;"
	onclick="
	n=1
	do{
		if(document.getElementById('rgn_summary'+n)==null){
			n= 0;		
		}else if(document.getElementById('rgn_summary'+n).style.display=='block'){
			document.getElementById('rgn_summary'+n).style.display='none';
			document.getElementById('rgn_content'+n).style.display='block';
			document.getElementById('rgn_bracket'+n).style.borderStyle='solid none solid solid';
			document.getElementById('rgn_button'+n).innerHTML='-';
			n++;
		}else if(document.getElementById('rgn_summary'+n).style.display=='none'){
			n++;
		}
		else{
			n= 0;
		} 
	} while( n!= 0 )
	">全て開く</span>
<!--
-->
</td></tr>
<tr>
<td valign=top>
<!--
-->
	<span id=rgn_opener style="cursor:pointer;border:gray 1px solid;"
	onclick="
	n=1
	do{
		if(document.getElementById('rgn_summary'+n)==null){
			n= 0;		
		}else if(document.getElementById('rgn_summary'+n).style.display=='block'){
			n++;
		}else if(document.getElementById('rgn_summary'+n).style.display=='none'){
			document.getElementById('rgn_summary'+n).style.display='block';
			document.getElementById('rgn_content'+n).style.display='none';
			document.getElementById('rgn_bracket'+n).style.borderStyle='none';
			document.getElementById('rgn_button'+n).innerHTML='+';
			n++;
		}
		else{
			n= 0;
		} 
	} while( n!= 0 )
	">全て閉じる</span>
<!--
-->
</td>
</tr>
</table>
EOD;
	}


} // end class RegiongroupPluginHTMLBuilder

?>
