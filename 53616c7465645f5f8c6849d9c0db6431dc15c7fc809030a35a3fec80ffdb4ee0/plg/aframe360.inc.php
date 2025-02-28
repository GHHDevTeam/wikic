<?php
// PukiWiki - Yet another WikiWikiWeb clone.
// aframe360.inc.php  Ver 1.1 2019/04/07 H.Tomose
//
// RICOH Thetaなどで撮影作成できる360度パノラマ画像を、
// Pukiwikiに埋め込み表示するためのプラグインです。
//
// Javascriptライブラリ A-Frame(https://aframe.io/) を利用します。
//-----
// usage:
// #aframe360(image[,w:nnn][,h:nnn][,x:nnn][,y:nnn][,z:nnn][,xmp])
//	image: 360度画像イメージファイル名。
//    「このプラグインが置かれたページに貼付された画像名」または「URL指定」する。
//	w: 表示幅(pixel)。初期値は 600.
//	h: 表示高さ(pixel)。初期値は 300.
//  x,y,z : 傾き情報。degree(0～360°)
//  xmp: 画像ファイル上のxmpmetaデータにある傾き情報を利用する指定。
//　　　　　x,y,z との指定は「パラメータ並び上の後優先」。
//　xmptest: xmp 指定に加えて、取り出した傾き情報を画像上にテキスト表示します。
//-----

function plugin_aframe360_convert()
{

	global $script, $vars;

	// パラメータ初期値の定義。init で定義してもよいが、仮にこちらで。
	$aframe_width="600px";
	$aframe_height="300px";
	$aframe_x="0";
	$aframe_y="0";
	$aframe_z="0";

	$aframe_xmp = false;	// xmpメタ情報を使うなら true
	$aframe_xmp_out = false;	// xmpメタ情報を文字列戻しするなら true
	$aframe_xmp_x=false;	// xmp と利用者指定のどちらを優先するか。true なら直指定優先
	$aframe_xmp_y=false;
	$aframe_xmp_z=false;
	$aframe_xmp_txt= "";	// 情報表示用。現時点では未使用。
	// JavaScriptライブラリを埋め込む都合上、「最初の１つだけ」判定のために個数カウント。
	static $aframe_numbers;
	if (! isset($aframe_numbers[$vars['page']])) $aframe_numbers[$vars['page']] = 0;
	$aframe_num = $aframe_numbers[$vars['page']]++;

	// 引数の取り出し
	$args=func_get_args();

	$imagename = array_shift ($args);
	foreach($args as $prm){

		if(strpos($prm,'w:')!==false) {
			$tmp = intval(substr($prm,1+strpos($prm,':')));
			// これが有効な数値なら、採用。
			if($tmp>100 and $tmp<1200){ $aframe_width=strval($tmp)."px";}
		}
		else if(strpos($prm,'h:')!==false) {
			$tmp = substr($prm,1+strpos($prm,':'));
			// これが有効な値でなかったら、「指定なし」扱いにする。
			if($tmp>100 and $tmp<1200){ $aframe_height=strval($tmp)."px";}
		}
		else if(strpos($prm,'x:')!==false) {
			$tmp = substr($prm,1+strpos($prm,':'));
			$aframe_x=strval($tmp);

			$aframe_xmp_x=$aframe_xmp;
		}
		else if(strpos($prm,'y:')!==false) {
			$tmp = substr($prm,1+strpos($prm,':'));
			$aframe_y=strval($tmp);
			$aframe_xmp_y=$aframe_xmp;
		}
		else if(strpos($prm,'z:')!==false) {
			$tmp = substr($prm,1+strpos($prm,':'));
			$aframe_z=strval($tmp);
			$aframe_xmp_z=$aframe_xmp;
		}
		else if(strpos($prm,'xmp')!==false) {
			// xmpmeta データからロール・ピッチ・ヨーを取り出すモード。
			// ここではモードだけセットしておく。
			$aframe_xmp= true;
			// テスト指定なら、テキスト出力モード。
			if(strpos($prm,'xmptest')!==false){
				$aframe_xmp_out=true;
			}
		}

		
	}
	
	// 指定された画像の確認。まず URLか否か確認
	$is_url = is_url($imagename);

	// url でなければ添付ファイルの確認。
	// 指定された画像があるか。なければエラー。
	if(! $is_url) {
	
		// 添付ファイルのあるページ: defaultは現在のページ名
		$page = isset($vars['page']) ? $vars['page'] : '';
		$files = UPLOAD_DIR . encode($page) . '_' . encode($imagename);
		$is_file = is_file($files);
		if(!$is_file){
			return "error: #aframe360, nofile.";
		}
		// ファイルがある。表示用URL作成。
		$url = $script . '?plugin=ref' . '&amp;page=' . rawurlencode($page) .
						'&amp;src=' . rawurlencode($imagename); // Show its filename at the last
	}else{
		// URL指定。指定されたパラメータを単純にURLにする。
		$url=$imagename;
		$files=$imagename;

	}

	if($aframe_xmp){
		// xmp データを使うように要求されている。ファイルから傾きメタデータを取り出す。
		// 指定ファイルの読み込み。外部サーバのためのタイムアウト処理類を加えておく。
		$context = stream_context_create(array(
			'http' => array(
				'ignore_errors' => true, 
				'timeout' =>10.0,
				'header' => 'User-Agent: Junyard.')
				));

		$content = file_get_contents($files,false, $context);
		preg_match("/[0-9]{3}/", $http_response_header[0], $stcode);
 		// http エラーコード $aframe_xmp_txt= strval($stcode[0]);
		// とはいえ、今回の実装ではあまり気にしない。

		// とりだしたファイル内に xmpデータがあるかのチェック。
		$xmp_data_start = strpos($content, '<x:xmpmeta');
		$xmp_data_end   = strpos($content, '</x:xmpmeta>');

		if( !(($xmp_data_end===false) or ($xmp_data_start===false))){
			// とりあえずメタデータはあるので、その解析。
			$xmp_length     = $xmp_data_end - $xmp_data_start;
			$xmp_data       = substr($content, $xmp_data_start, $xmp_length + 12);
			$xmp            = simplexml_load_string($xmp_data);

			if(!($xmp===false) ){ // XMPとして妥当と判断
				// simpleXMP では namespace付要素を取り出すのが面倒なので、正規表現で切り出す。
				if( preg_match("/<GPano:PoseRollDegrees>(.+)</",$xmp_data,$matches)){
					$tmp = $matches[1]*-1;
					if(!$aframe_xmp_x) $aframe_x =$tmp;
					if($aframe_xmp_out) $aframe_xmp_txt .= "x:".$tmp.",";
				}
				if( preg_match("/<GPano:PoseHeadingDegrees>(.+)</",$xmp_data,$matches)){
					if(!$aframe_xmp_y) $aframe_y =$matches[1];
					if($aframe_xmp_out) $aframe_xmp_txt .= "y:".$matches[1].",";
				}
				if( preg_match("/<GPano:PosePitchDegrees>(.+)</",$xmp_data,$matches)){
					$tmp = $matches[1];
					if(!$aframe_xmp_z) $aframe_z =$tmp;
					if($aframe_xmp_out) $aframe_xmp_txt .= "z:".$tmp;
				}
				
			}
		}
	}


	// html構築。
	$html = "";	
	// 「1回のみ」実施すべき、Javascriptライブラリのインポート処理。
	if($aframe_num==0){
	$html .=<<<EOD
    <script src="https://aframe.io/releases/0.9.0/aframe.min.js"></script>

EOD;
	}
	// 実体部分。
	$html .=<<<EOD
	<p>$aframe_xmp_txt</p>
	<div id="div_aframe_$aframe_num" style="height:$aframe_height; width:$aframe_width;">
    <a-scene embedded>
      <a-sky src='$url' rotation="$aframe_x $aframe_y $aframe_z"></a-sky>
    </a-scene>
	</div>
EOD;

	return $html;

}
?>