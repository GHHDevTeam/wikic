<?php
function plugin_wikilist_convert(){
$wikifiles = [];
$i = 0;
foreach (new DirectoryIterator('../../') as $fileinfo) {
    if($i >= 30){
        break;
    }
    if (!$fileinfo->isDot()) {
        $filename = $fileinfo->getFilename();
        if ($filename != "53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0" && $filename != "index.php" && $filename != "debug12480143.php" && $filename != "pukiwiki.zip" && $filename != "dokuwiki.zip" && $filename != ".htaccess"){
            $data = null;$split_data = null;$split_data2 = null;
            $file_handler = fopen('../account.txt', r);
            while ($data = fgets($file_handler)) {
                $split_data2 = preg_split('/\'/', $data);
                if ($filename == $split_data2[5]){
                    $split_data = preg_split('/\'/', $data);
                }
            }
            fclose($file_handler);
            if ($split_data[9] == "pukiwiki"){
                $wikifiles[] = ['name' => $filename, 'date' => filemtime("../../" . $filename."/wiki")];
            }else if ($split_data[9] == "dokuwiki"){
                $wikifiles[] = ['name' => $filename, 'date' => filemtime("../../" . $filename."/data/pages/wiki")];
            }else{}
            $i++;
        }
    }
}
$latestwikifiles = [];
foreach($wikifiles as $key => $value){
  $latestwikifiles[$key] = $value['date'];
}
array_multisort($latestwikifiles, SORT_DESC, $wikifiles);
foreach($wikifiles as $key => $value){
    $data = null;$split_data = null;$split_data2 = null;
    $file_handler = fopen('../account.txt', r);
    while ($data = fgets($file_handler)) {
        $split_data2 = preg_split('/\'/', $data);
        if ($value['name'] == $split_data2[5]){
            $split_data = preg_split('/\'/', $data);
        }
    }
    fclose($file_handler);
    if (mb_strlen($split_data[7]) >= 25){
        $split_data[7] = mb_substr($split_data[7], 0, 25) . "...";
    }
    $html .= "<font size=\"5\"><a href=\"./".$value['name']."/\" style=\"color:black;display:inline-block;\">".$split_data[7]."</a></font> <font color=\"#666666\">".date("Y/m/d H:i:s",$value['date'])."</font><br />";
}
return $html;
}
