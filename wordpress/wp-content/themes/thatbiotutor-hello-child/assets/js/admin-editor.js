/* Reuse the saved mark format for both text colours and paragraph highlighting. */
(function () {
  if (typeof tinymce === 'undefined') { return; }
  tinymce.PluginManager.add('tbt_highlight', function (editor) {
    editor.on('init', function () {
      editor.formatter.register('tbt_highlight', { inline: 'mark' });
      editor.formatter.register('tbt_red', { inline: 'mark', attributes: { 'data-tone': 'red' } });
      editor.formatter.register('underline', { inline: 'u', exact: true });

      var field = editor.getElement().closest('.acf-field');
      if (!field) { return; }
      if (field.classList.contains('tbt-text-accent-dark')) {
        editor.dom.addStyle('.mce-content-body{background:#0e371d;color:#fff} .mce-content-body mark,.mce-content-body mark[data-tone=red]{background:transparent;color:#8fe17e;padding:0}');
      } else if (field.classList.contains('tbt-text-accent-hero')) {
        editor.dom.addStyle('.mce-content-body mark{background:transparent;color:#43b43a;padding:0} .mce-content-body mark[data-tone=red]{background:transparent;color:#e62c2c}');
      } else if (field.classList.contains('tbt-text-accent-light')) {
        editor.dom.addStyle('.mce-content-body mark,.mce-content-body mark[data-tone=red]{background:transparent;color:#2f8a27;padding:0}');
      }
    });

    function emphasisButton(name, text, tooltip, format) {
      editor.addButton(name, {
        text: text,
        tooltip: tooltip,
        onclick: function () { editor.formatter.toggle(format); },
        onPostRender: function () {
          var button = this;
          editor.on('init', function () {
            editor.formatter.formatChanged(format, function (state) { button.active(state); });
          });
        }
      });
    }

    emphasisButton('tbt_highlight', 'Highlight', 'Highlight in green', 'tbt_highlight');
    emphasisButton('tbt_green_text', 'Green text', 'Green text', 'tbt_highlight');
    emphasisButton('tbt_red', 'Red text', 'Red text', 'tbt_red');
  });
}());
