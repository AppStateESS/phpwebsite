<!doctype html>
<html lang=en>
    <head>
        <meta charset=utf-8>
        <title>CKEditor Filecabinet</title>
        <link href="{SOURCE_HTTP}mod/filecabinet/templates/ckeditor/style.css" rel="stylesheet" type="text/css">
        <script src="{SOURCE_HTTP}javascript/jquery/jquery.js" type="text/javascript"></script>
        <script src="{SOURCE_HTTP}javascript/jquery_ui/jquery-ui.js" type="text/javascript"></script>
        <script src="{SOURCE_HTTP}mod/filecabinet/templates/ckeditor/js/script.js"></script>
        <script src="{SOURCE_HTTP}javascript/flowplayer/flowplayer-3.2.10.min.js"></script>
        <script>var folder_open = '{SOURCE_HTTP}mod/filecabinet/templates/ckeditor/images/folder_open.png';
            var folder_closed = '{SOURCE_HTTP}mod/filecabinet/templates/ckeditor/images/directory.png';
            var folder_type = '{FOLDER_TYPE}';
        </script>
        <!-- BEGIN errors --><script>alert('{ERRORS}');</script> <!-- END errors -->
    </head>
    <body>
        <div id="folders">
            <div id="file-type-buttons">
                <select id="folder-type">
                    <option value="1">Images</option>
                    <option value="2">Documents</option>
                    <option value="3">Multimedia</option>
                </select>
            </div>
            <div id="folder-listing">
                {FOLDER_LISTING}
            </div>
        </div>
        <div id="tools">
            <table>
                <tr>
                    <td style="width : 200px">Current folder: <span id="current-folder"></span></td>
                    <td id="folder-form" style="display : none">{START_FORM}
                        <input type="hidden" id="folder-id" name="folder_id" value="" />
                        <input type="hidden" id="ftype" name="ftype" value="" />
                        {FILENAME}
                        {SUBMIT}
                        {END_FORM}
                    </td>
                </tr>
            </table>
        </div>
        <div id="files">{FILE_LISTING}</div>
    </body>
</html>