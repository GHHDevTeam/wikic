<?php
if(isset($_GET['file'])){
    $safefile = false;
    foreach (new DirectoryIterator('./') as $fileinfo) {
        if (!$fileinfo->isDot()) {
            $filename = $fileinfo->getFilename();
            if ($filename == $_GET['file']){
                $fpath = './'.$_GET['file'];
                $fname = $_GET['file'];
                header('X-Content-Type-Options: nosniff');
                header('Content-Type: application/force-download');
                header('Content-Length: '.filesize($fpath));
                header('Content-disposition: attachment; filename="'.basename($fname).'"');
                header('Connection: close');
                readfile($fpath);
                $safefile = true;
            }
        }
    }
    if ($safefile == true){
        echo "ダウンロードが完了しました。";
        exit;
    }else if ($safefile == false){
        echo "ダウンロードに失敗しました。";
        exit;
    }
    

}
?>
<html lang="ja-jp">
    <head>
        <title>ディレクトリファイルダウンローダー</title>
    </head>
    <body>
<?php
foreach (new DirectoryIterator('./') as $fileinfo) {
    if (!$fileinfo->isDot()) {
        $filename = $fileinfo->getFilename();
        if ($filename != "index.php"){
            echo "<a href=\"./?file=".$filename."\">".$filename."</a><br>";
        }
    }
}
?>
    </body>
</html>