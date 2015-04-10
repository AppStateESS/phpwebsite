CKEDITOR.dialog.add('multimediaDialog', function(editor) {
    return {
        title: 'Filecabinet Multimedia',
        resizable: CKEDITOR.DIALOG_RESIZE_BOTH,
        contents: [
            {
                id: 'tab1',
                label: 'File Cabinet Multimedia',
                title: 'File Cabinet Multimedia',
                elements: [
                    {
                        type: 'iframe',
                        src: editor.config.RootPath + 'index.php?module=filecabinet&ckop=form&ftype=3',
                        width: 1024, height: 600 - (CKEDITOR.env.ie ? 10 : 0)
                    }
                ]
            }
        ]
    }
});