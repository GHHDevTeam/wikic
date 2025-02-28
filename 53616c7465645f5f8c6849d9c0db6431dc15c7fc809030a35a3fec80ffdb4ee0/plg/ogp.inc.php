 <?php

// 'ogp' plugin for PukiWiki
// Twitter: @m0370
 
// ver1.31 (2020.5.5)
// キャッシュ関連のバグを修正

// ver 1.4 (2020.7.16)
// HTMLパース,noimg対応。画像がない場合や画像ファイルのサイズが'0'の場合はnoimgとなります。
// 一部のWordpressプラグインなど、OGP画像がjpg拡張子になっていても中身がwebPやgzipの場合にキャッシュ画像が表示できない問題があります。
 

function plugin_ogp_convert()
{
	$ogpurl = func_get_args();
	$uri = get_script_uri();
	$ogpurlsp = (explode('://', $ogpurl[0]));
	$ogpurlmd = md5($ogpurlsp[1]);
	$datcache = CACHE_DIR . 'ogp/' . $ogpurlmd . '.dat';
	$gifcache = CACHE_DIR . 'ogp/' . $ogpurlmd . '.gif';
	$jpgcache = CACHE_DIR . 'ogp/' . $ogpurlmd . '.jpg';
	$pngcache = CACHE_DIR . 'ogp/' . $ogpurlmd . '.png';
	
	if(file_exists($pngcache)) { $imgcache = $pngcache ; }
	else if(file_exists($gifcache)) { $imgcache = $gifcache ; }
	else { $imgcache = $jpgcache ; }
	
	if(file_exists($datcache) && file_exists($imgcache)) {
		$ogpcache = file_get_contents($datcache);
		$ogpcachearray = explode("<>", $ogpcache);
		$title = $ogpcachearray[0];
		$description = $ogpcachearray[1];
		$src = $imgcache ;
		$imgfile = file_get_contents($imgcache) ;
	} else {
	    require_once(PLUGIN_DIR.'opengraph.php');
	    $graph = OpenGraph::fetch($ogpurl[0]);    
	    if ($graph) {
	        $title = $graph->title;
	        $url = $graph->url;
	        $description = $graph->description;
	        $src = $graph->image;
	    
		    $title_check = utf8_decode($title);
		    $description_check = utf8_decode($description);
		    if(mb_detect_encoding($title_check) == 'UTF-8'){
		        $title = $title_check; // 文字化け解消
		    }
		    if(mb_detect_encoding($description_check) == 'UTF-8'){
		        $description = $description_check; // 文字化け解消
		    }
		    
		    $detects = array('ASCII','EUC-JP','SJIS','JIS','CP51932','UTF-16','ISO-8859-1');
		    
		    // 上記以外でもUTF-8以外の文字コードが渡ってきてた場合、UTF-8に変換する
		    if(mb_detect_encoding($title) != 'UTF-8'){
		        $title = mb_convert_encoding($title, 'UTF-8', mb_detect_encoding($title, $detects, true));
		    }
		    if(mb_detect_encoding($description) != 'UTF-8'){
		        $description = mb_convert_encoding($description, 'UTF-8', mb_detect_encoding($description, $detects, true));
		    }
		    
			$imgfile = file_get_contents($src);
			$imginfo = getimagesize($src);
			file_put_contents($datcache, $title . '<>' . $description . '<>' . $ogpurl[0]);
			$filetype = $imginfo[2];
			if( $filetype == 1 ){
				file_put_contents($gifcache, $imgfile) ;
			} else if ( $filetype == 3 ){
				file_put_contents($pngcache, $imgfile) ;
			} else {
				file_put_contents($jpgcache, $imgfile) ;
			} //どの拡張子でもない場合どうするか(webp,gzip)、jpg拡張子のgzファイルでエラーが出る
		} else return '#ogp Error: Page not found.';
	}
	
	if( filesize($imgcache) == 0 ){
		$ogpurl[2] = "ogp-noimg" ;
	}
	
	if($ogpurl[1] == "amp"){
	    $imgtag = 'amp-img class="ogp-img" layout="fill"' ;
	} else if($ogpurl[1] == "noimg") {
		$ogpurl[2] == "ogp-noimg" ;
		$imgtag = 'amp-img class="ogp-noimg" layout="nodisplay" ' ;
	} else {
	    $imgtag = 'img class="ogp-img"' ;
//		$imgtag = 'amp-img class="ogp-img ' . $ogpclass . '" layout="fill"' ; //常時AMP
	}
	
	$ogpurl[0] = htmlspecialchars($ogpurl[0]);
	
	return <<<EOD
	<div class="ogp $ogpclass">
	<div class="ogp-img-box $ogpurl[2]"><$imgtag layout="fill" src="$src" alt="$title" /></div>
	<div class="ogp-title">$title</div>
	<div class="ogp-description">$description</div>
	<div class="ogp-url">$ogpurl[0]</div>
	<a class ="ogp-link" href="$ogpurl[0]" target="_blank"></a>
	</div>

	EOD;
}

?>
<style>
/*OGP スタイルシート*/
.ogp {
    position: relative;
    z-index: 1; /* 必要であればリンク要素の重なりのベース順序指定 */
    word-wrap: break-word;
	word-break: break-all;
	border: 1px solid rgba(0,0,0,.1);
	border-radius: 4px;
	padding: 10px;
	margin:20px 2px;
	min-height: 100px;
	max-height: 280px;
	max-width: 480px; 
	overflow: hidden;
}
.ogp-link {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    text-indent:-999px;
    z-index: 2; /* 必要であればリンク要素の重なりのベース順序指定 */
}
.ogp-title{
	font-size: 100%;
	font-weight:bold;
	margin: 0 0 2px;
	line-height: 1.3em;
	color: #000;
	overflow: hidden;
	display: -webkit-box;
	-webkit-line-clamp: 2;
	-webkit-box-orient: vertical; 
}
.ogp-description{
	line-height: 1.5em;
	font-size: 80%;
	color: #000;
	overflow: hidden;
	display: -webkit-box;
	-webkit-line-clamp: 3;
	-webkit-box-orient: vertical; 
}
.ogp-url{
	line-height: 1.8em;
	font-size: 80%;
	color: #7b8387;
	overflow: hidden;
	display: -webkit-box;
	-webkit-line-clamp: 1;
	-webkit-box-orient: vertical; 
}
.ogp-img-box{
	float: right;
	position: relative;
	width: 100px;
	height: 100px;
	margin-left:10px;
}
.ogp-noimg{display:none;}
.ogp-img img{object-fit: cover;}
img.ogp-img{width: 100px;height: 100px;object-fit: cover;}
</style>