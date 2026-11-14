/* ThatBioTutor editor plugin: a Highlight button (green <mark>) and an Underline that writes <u>,
   matching how the site styles emphasis. Loaded only inside the WordPress editor. */
(function () {
  if (typeof tinymce === 'undefined') { return; }
  tinymce.PluginManager.add('tbt_highlight', function (editor) {
    editor.on('init', function () {
      editor.formatter.register('tbt_highlight', { inline: 'mark' });
      editor.formatter.register('tbt_red', { inline: 'mark', attributes: { 'data-tone': 'red' } });
      editor.formatter.register('underline', { inline: 'u', exact: true });
    });
    editor.addButton('tbt_red', {
      text: 'Red',
      tooltip: 'Highlight in red (Home title only)',
      onclick: function () { editor.formatter.toggle('tbt_red'); }
    });
    editor.addButton('tbt_highlight', {
      text: 'Highlight',
      tooltip: 'Highlight in green',
      onclick: function () { editor.formatter.toggle('tbt_highlight'); },
      onPostRender: function () {
        var button = this;
        editor.on('init', function () {
          editor.formatter.formatChanged('tbt_highlight', function (state) { button.active(state); });
        });
      }
    });
  });
}());
