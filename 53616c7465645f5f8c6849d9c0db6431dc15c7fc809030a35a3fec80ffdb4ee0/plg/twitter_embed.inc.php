<?php
/*
 * PukiWiki twitter_embed plugin
 */

function plugin_twitter_embed_init()
{
        global $head_tags;
        $head_tags[] = <<<EOF
<script>window.twttr = (function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0],
  t = window.twttr || {};
  if (d.getElementById(id)) return t;
  js = d.createElement(s);
  js.id = id;
  js.src = "https://platform.twitter.com/widgets.js";
  fjs.parentNode.insertBefore(js, fjs);
  t._e = [];
  t.ready = function(f) {
    t._e.push(f);
  };
  return t;
}(document, "script", "twitter-wjs"));</script>
EOF;
}

function plugin_twitter_embed_convert()
{
        $tweet_id = null;
        if (func_num_args())
        {
                $args = func_get_args();
                $tweet_id = intval($args[0]);
        }
        if (!$tweet_id) {
                return '#twitter_embed(): Tweet ID needed';
        }
        $html = '<div class="twitter-embed" id="twitter-embed-'. $tweet_id .'"></div>';
        $html .= <<<EOF
<script type="text/javascript">
twttr.ready(function (){
twttr.widgets.createTweet(
  '$tweet_id',
  document.getElementById('twitter-embed-$tweet_id'),
  {});
});
</script>
EOF;
        return $html;
}
?>