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
                <select id="folder-type">
                    <option value="image">Images</option>
                    <option value="document">Documents</option>
                    <option value="multimedia">Multimedia</option>
                </select>
                <!--<ul><li>{IMAGE_BUTTON}</li><li>{DOCUMENT_BUTTON}</li><li>{MEDIA_BUTTON}</li></ul>-->
            </div>
            <div id="folder-listing">
                {FOLDER_LISTING}
            </div>
        </div>
        <div id="files"></div>
    </body>
</html>