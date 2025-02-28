<?php
function dirarray(){
  $dir_array = array("logparanoia","wiki","attach","diff","backup","plugin");
  return $dir_array;
}
function plugin_webdl_convert(){
  $result = dirlist();
  return $result;
}
function dirlist(){
  $dir_array = dirarray();

  $result = "<form method=\"post\" action=\"index.php?plugin=webdl\">";
  $result .= "<select name = \"directory\">\n";
  foreach ($dir_array as $dir_name){
    if (is_dir($dir_name) == TRUE){
      $result .= "<option>$dir_name</option>\n";
    }
  }
  $result .= "</select>\n";
  $result .= "<input type=\"submit\" name=\"type\" value=\"SELECT DIRECTORY\" />\n";
  $result .= "</form>\n";
  return $result;
}

function plugin_webdl_action(){
  global $vars;
  $dir_name = $vars["directory"];
  $file_name = $vars["downloadfile"];
  $page = $vars["page"];
  //セキュリティチェック
  check_readable($page, TRUE, TRUE);
  $dir_array = dirarray();
  $safety = "off";
  foreach($dir_array as $dir_chk){
    if ($dir_chk == $dir_name){
      $safety = "on";
    }
  }
  if ($safety != "on"){
    exit;
  }elseif (preg_match("/\//", $file_name)){
    exit;
  }

  if($file_name != ""){
    transport($dir_name,$file_name);
  }elseif ($dir_name != ""){
    $result = filelist($dir_name);
    return array('msg' => "webdl->$dir_name", 'body' => "$result");
  }
}
function filelist($dir_name){
  if (!($dir_content = opendir($dir_name))) {
    die;
  }
  $result = "<form method=\"post\" action=\"index.php?plugin=webdl\">\n";
  $result .= "<input type=hidden name = \"directory\" value=\"$dir_name\">\n";
  $result .= "<select name = \"downloadfile\">\n";
  while ($temp = readdir($dir_content)) {
    $filecheck = $dir_name."/".$temp;
    if (is_file($filecheck) == TRUE){
      $result .= "<option>$temp</option>\n";
    }
  }
  $result .= "</select>\n";
  $result .= "<input type=\"submit\" name=\"type\" value=\"download\" />\n";
  $result .= "</form>\n";
  closedir($dir_content);
  return $result;
}
function transport($dir_name,$file_name){
  chdir($dir_name);
  $filesize = filesize("$file_name");
  header ("Content-Disposition: attachment; filename=$file_name");
  //header ("Content-type: application/octet-stream");
  header ("Content-type: application/webdl");
  header ("Content-length:$filesize");
  readfile ($file_name);
  exit;
}
?>