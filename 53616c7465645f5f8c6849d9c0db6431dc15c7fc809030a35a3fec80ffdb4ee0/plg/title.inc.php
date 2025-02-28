<?php
// $Id: title.inc.php,v 1.0 2006/04/07 12:00:00 DOBON! Exp $
//
// title plugin
//
// http://dobon.net/
 
define('PLUGIN_TITLE_USAGE', '#title(page_title)');
 
function plugin_title_convert()
{
    global $title;
    
    if (func_num_args() != 1) return PLUGIN_TITLE_USAGE;
    $args = func_get_args();
    
    $title = htmlsc($args[0]);
 
    return "";
}