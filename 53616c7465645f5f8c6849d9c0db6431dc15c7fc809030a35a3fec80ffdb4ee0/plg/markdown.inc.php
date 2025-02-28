<?php

function plugin_markdown_header() {
    return <<<'EOS'
<script src="https://unpkg.com/markdown-it/dist/markdown-it.min.js"></script>
<script src="https://unpkg.com/markdown-it-ins/dist/markdown-it-ins.min.js"></script>
<script src="https://unpkg.com/markdown-it-mark/dist/markdown-it-mark.min.js"></script>
<script src="https://unpkg.com/markdown-it-sub/dist/markdown-it-sub.min.js"></script>
<script src="https://unpkg.com/markdown-it-sup/dist/markdown-it-sup.min.js"></script>
<script src="https://unpkg.com/markdown-it-footnote/dist/markdown-it-footnote.min.js"></script>
<script src="https://unpkg.com/markdown-it-deflist/dist/markdown-it-deflist.min.js"></script>
<script src="https://unpkg.com/markdown-it-abbr/dist/markdown-it-abbr.min.js"></script>
<script src="https://unpkg.com/markdown-it-emoji/dist/markdown-it-emoji.min.js"></script>
<script src="https://twemoji.maxcdn.com/v/latest/twemoji.min.js" crossorigin="anonymous"></script>
<script src="https://unpkg.com/highlightjs"></script>
<style>
  /* table */
  #body table {
    border-collapse: collapse;
    border-spacing: 0;
    margin: 0.8em;
  }
  #body td, th {
    border: 1px solid gray;
    padding: 0.4em;
  }
  #body th {
    background-color: #eef5ff;
  }
  #body td, #body th {
    font-size: 1em;
  }

  /* emoji */
  .emoji {
    height: 1.2em;
  }

  /* code */
  :not(pre) > code {
    color: #dd3333;
    font-weight: bold;
    padding: 0.125em 0.25em;
    margin: 0.25em;
    border-radius: 3px;
    border: solid 1px lightgray;
  }
</style>
<script>
  document.addEventListener("DOMContentLoaded", () => {
    // Load GitHub style for <code> tag
    const style = document.createElement('link');
    style.href = 'https://unpkg.com/highlightjs/styles/github-gist.css';
    style.type = 'text/css';
    style.rel = 'stylesheet';
    document.head.appendChild(style);

    const mdBodies = document.querySelectorAll('.md-body');
    mdBodies.forEach(mdBody => {
      const md = window.markdownit({
        linkify: true,
        langPrefix:   'language-',
        highlight: function (str, lang) {
          if (lang && hljs.getLanguage(lang)) {
            try {
              return '<pre class="hljs"><code>' +
                hljs.highlight(lang, str, true).value +
               '</code></pre>';
            } catch (__) {}
          }
          return '<pre class="hljs"><code>' + md.utils.escapeHtml(str) + '</code></pre>';
        },
      })
      .use(window.markdownitIns)
      .use(window.markdownitMark)
      .use(window.markdownitSub)
      .use(window.markdownitSup)
      .use(window.markdownitFootnote)
      .use(window.markdownitDeflist)
      .use(window.markdownitAbbr)
      .use(window.markdownitEmoji);
      md.renderer.rules.emoji = function(token, idx) {
        return twemoji.parse(token[idx].content);
      };
      mdBody.innerHTML = md.render(mdBody.textContent);
    });
  });
</script>
EOS;
}

function plugin_markdown_convert() {
    $header = "";
    if (!defined("PLUGIN_MARKDOWN_LOADED")) {
        define("PLUGIN_MARKDOWN_LOADED", "LOADED");
        $header = plugin_markdown_header();
    }

    $args = func_get_args();
    $body = array_pop($args);

    return $header . "<div class=\"md-body\">" . $body . "</div>";
}

?>
