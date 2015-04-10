CKEDITOR.plugins.add('fc_multimedia', {
    icons: 'fc_multimedia',
    init: function(editor) {
        var mypath = this.path;
        editor.addCommand('fc_multimedia', new CKEDITOR.dialogCommand('multimediaDialog'));

        editor.ui.addButton('File Cabinet Multimedia', {
            label: 'Insert multimedia',
            command: 'fc_multimedia',
            icon: mypath + 'icons/fc_multimedia.png',
            toolbar: 'insert'
        });

        CKEDITOR.dialog.add('multimediaDialog', this.path + 'dialogs/fc_multimedia.js');
    }
});