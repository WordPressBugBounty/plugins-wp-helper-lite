jQuery(document).ready(function ($) {
    var editors = [
        { id: 'css-mobile' },
        { id: 'css-tablet' },
        { id: 'css-desktop' },
    ];
    editors.forEach(function (cfg) {
        var el = document.getElementById(cfg.id);
        if (!el) return;
        var cm = CodeMirror.fromTextArea(el, {
            lineNumbers: true,
            mode: 'css',
            theme: 'default',
            extraKeys: { 'Ctrl-Space': 'autocomplete' },
            lineWrapping: true,
            autofocus: false,
        });
        cm.on('change', function () { cm.save(); });
    });
});
