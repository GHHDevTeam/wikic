<?php
define(ANALYTICS_ID, 'UA-0000000-0'); // 設定したいアナリティクスID
function plugin_google_analytics_init()
{ 
$_id = ANALYTICS_ID;
return <<<_TAG
<!- GoogleAnalyticsStart -->
  <script type="text/javascript"><!--
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', '{$_id}', 'auto');
  ga('send', 'pageview');
  // --></script>
<!- GoogleAnalyticsEnd -->
_TAG;
}
?>