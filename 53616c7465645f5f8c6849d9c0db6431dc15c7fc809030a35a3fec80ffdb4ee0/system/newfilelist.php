<html lang="ja_jp">
    <head>
    <script type="text/javascript">
<!--

function deletecheck(filename){
	if(window.confirm('本当に削除しますか？')){
		location.href = "./controlpanel.php?filedelete=" + filename; // example_confirm.html へジャンプ
	}
	else{
		window.alert('削除をキャンセルしました。'); // 警告ダイアログを表示
	}
}
</script>
    </head>
    <body>
        <h2>ファイルマネージャー</h2>
        txtファイル、cssファイルの表示を許可しています。セキュリティー上などの理由によりプログラムのファイルなどは伏せています。<br /><br />
<?php
$fordir = './'.$loginid.'/'.$_GET['filelist'];
$dirname = dirname($_GET['filelist']);
if($dirname == "."){
    $dirname = "";
}else{
    $dirname = $dirname . "/";
}
echo "<a href=\"./controlpanel.php?filelist=".$dirname."\">../</a><br>";
foreach (new DirectoryIterator($fordir) as $fileinfo) {
    if ($fileinfo->isFile()) {
        $fullname = $_GET['filelist'] . $fileinfo->getFilename();
        $filename = $fileinfo->getFilename();
        $ext = pathinfo($filename)['extension'];
        if ($ext == "txt"||$ext == "css"){
            echo "<a href=\"./controlpanel.php?fileeditor=".$fullname."\">".$filename." (ファイル編集)</a> - <a href=\"./controlpanel.php?pagedownloader=".$fullname."\">[ダウンロード]</a> - <a href=\"javascript:deletecheck('".$fullname."');\">[削除]</a><br>";
        }
    }elseif($fileinfo->isDir()){
        $filename = $fileinfo->getFilename();
        if ($filename == "." || $filename == ".."){
        }else{
            echo "<a href=\"./controlpanel.php?filelist=".$_GET['filelist'].$filename."/\">".$filename."/</a><br>";
        }
    }
}
?>
    </body>
</html>