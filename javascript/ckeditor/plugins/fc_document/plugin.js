CKEDITOR.plugins.add('fc_document', {
    icons: 'fc_document',
    init: function(editor) {
        var mypath = this.path;
        editor.addCommand('fc_document', new CKEDITOR.dialogCommand('documentDialog'));

        editor.ui.addButton('File Cabinet Documents', {
            label: 'Insert document',
            command: 'fc_document',
            icon: mypath + 'icons/fc_document.png',
            toolbar: 'insert'
        });

        CKEDITOR.dialog.add('documentDialog', this.path + 'dialogs/fc_document.js');
    }
});