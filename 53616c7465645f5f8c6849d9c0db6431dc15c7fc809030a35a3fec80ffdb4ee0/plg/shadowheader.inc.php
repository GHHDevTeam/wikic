<?php
function plugin_shadowheader_convert(){
 
 if(func_num_args()<2){
  return 'ERROR: argument shortage...';
 }
 $argv=func_get_args();
 $header_level=$argv[0];
 $header_str=$argv[1];
 $shadowheader=array();
 switch($header_level){
 case 1:
  $shadowheader[]='*'.$header_str;
  break;
 case 2:
  $shadowheader[]='**'.$header_str;
  break;
 case 3:
  $shadowheader[]='***'.$header_str;
  break;
 default:
  break;
 }
 return convert_html($shadowheader);
}
?>