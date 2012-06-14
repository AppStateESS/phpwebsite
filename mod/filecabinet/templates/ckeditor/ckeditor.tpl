<!doctype html>
<html lang=en>
    <head>
        <meta charset=utf-8>
        <title>CKEditor Filecabinet</title>
        <link href="{SOURCE_HTTP}mod/filecabinet/templates/ckeditor/style.css" rel="stylesheet" type="text/css">
        <script src="{SOURCE_HTTP}javascript/jquery/jquery.js" type="text/javascript"></script>
        <script src="{SOURCE_HTTP}javascript/jquery_ui/jquery-ui.js" type="text/javascript"></script>
        <script src="{SOURCE_HTTP}mod/filecabinet/templates/ckeditor/js/script.js"></script>
        <script>var folder_open = '{SOURCE_HTTP}mod/filecabinet/templates/ckeditor/images/folder_open.png';
            var folder_closed = '{SOURCE_HTTP}mod/filecabinet/templates/ckeditor/images/directory.png';</script>
    </head>
    <body>
        <div id="folders">
            <div id="file-type-buttons">
                {IMAGE_BUTTON} {DOCUMENT_BUTTON} {MEDIA_BUTTON}
            </div>
            <div id="folder-listing">
                {FOLDER_LISTING}
            </div>
        </div>
        <div id="files"></div>
    </body>
</html>