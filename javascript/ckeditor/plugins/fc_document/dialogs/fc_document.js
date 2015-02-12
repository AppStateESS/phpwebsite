CKEDITOR.dialog.add('documentDialog', function(editor) {
    return {
        title: 'Filecabinet Documents',
        resizable: CKEDITOR.DIALOG_RESIZE_BOTH,
        contents: [
            {
                id: 'tab1',
                label: 'File Cabinet Document',
                title: 'File Cabinet Document',
                accessKey: 'Q',
                elements: [
                    {
                        type: 'iframe',
                        src: editor.config.RootPath + 'index.php?module=filecabinet&ckop=form&ftype=2',
                        width: 1024, height: 600 - (CKEDITOR.env.ie ? 10 : 0)
                    }
                ]
            }
        ]
    }
});