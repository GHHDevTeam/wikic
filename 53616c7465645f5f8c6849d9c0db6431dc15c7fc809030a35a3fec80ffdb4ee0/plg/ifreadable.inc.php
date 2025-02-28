<?php
 
//ifreadable.inc.php
//Version 0.0.1
//作成者:どぼん！
 
function plugin_ifreadable_convert()
{
    $args = func_get_args();
    $str = array_pop($args);
 
    if (plugin_ifreadable_check($args))
        return convert_html(str_replace("\r", "\r\n", $str));
    else
        return '';
}
 
function plugin_ifreadable_inline()
{
    $args = func_get_args();
    $str = array_pop($args);
 
    if (plugin_ifreadable_check($args))
        return $str;
    else
        return '';
}
function plugin_ifreadable_check($args)
{
    $not_option = in_array('not', $args);
    
    return plugin_ifreadable_check_readable($args) xor $not_option;
}
 
function plugin_ifreadable_check_readable($args)
{
    $page = array_shift($args);
    if (empty($page))
        return false;
    
    $check_readable = !in_array('noreadable', $args);
    $check_editable = in_array('editable', $args);
    
    if ($check_readable && !check_readable($page, false, false))
        return  false;
    
    if ($check_editable && !check_editable($page, false, false))
        return  false;
    
    return true;
}
?>