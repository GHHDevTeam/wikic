<?php
function plugin_discord_convert(){
    $args = func_get_args();
    $serverid = $args[0];
    if (empty($serverid)){
        return "<p>#discord(ServerID)</p>";
    }
    $data = <<<EOD
    <iframe src="https&#58;//discordapp.com/widget?id=$serverid&theme=dark" width="350" height="500" allowtransparency="true" frameborder="0" sandbox="allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts"></iframe><br>
    EOD;
    return $data;
}
function plugin_discord_inline() {
	return call_user_func_array('plugin_discord_convert', func_get_args());
}
?>