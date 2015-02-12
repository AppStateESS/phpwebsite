CKEDITOR.plugins.add('fc_image', {
    icons: 'fc_image',
    init: function(editor) {
        var mypath = this.path;
        editor.addCommand('fc_image', new CKEDITOR.dialogCommand('imageDialog'));

        editor.ui.addButton('File Cabinet Images', {
            label: 'Insert image',
            command: 'fc_image',
            icon: mypath + 'icons/fc_image.png',
            toolbar: 'insert'
        });

        CKEDITOR.dialog.add('imageDialog', this.path + 'dialogs/fc_image.js');
    }
});