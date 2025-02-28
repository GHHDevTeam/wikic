<?php
//http://www.swetake.com/qrcode/php/qr_php.html を利用しております。
define('QR_CODE_LIB','../53616c7465645f5f8c6849d9c0db6431dc15c7fc809030a35a3fec80ffdb4ee0/plgfiles/lib/qrcode/qrgenerator.php');
//↑QRコードクラスライブラリのqrcode_img.phpのパス
function plugin_qrcode_convert(){
    $qrcodelib = QR_CODE_LIB;
    $args = func_get_args();
    $str = $args[0];
    if (empty($str)){
        return "<p>#qrcode(Abc あいう)</p>";
    }
    $data = <<<EOD
    <img src="$qrcodelib?d=$str"></img><br>
    EOD;
    return $data;
}
function plugin_qrcode_inline() {
	return call_user_func_array('plugin_qrcode_convert', func_get_args());
}
?>