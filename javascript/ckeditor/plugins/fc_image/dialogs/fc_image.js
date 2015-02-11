CKEDITOR.dialog.add('imageDialog', function(editor) {
    return {
        title: 'Filecabinet Images',
        resizable: CKEDITOR.DIALOG_RESIZE_BOTH,
        contents: [
            {
                id: 'tab1',
                label: 'File Cabinet Image',
                title: 'File Cabinet Image',
                elements: [
                    {
                        type: 'iframe',
                        src: editor.config.RootPath + 'index.php?module=filecabinet&ckop=form&ftype=1',
                        width: 1024, height: 600 - (CKEDITOR.env.ie ? 10 : 0)
                    }
                ]
            }
        ]
    }
});