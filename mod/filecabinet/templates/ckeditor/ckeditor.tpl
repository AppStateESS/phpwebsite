<!doctype html>
<html lang=en>
    <head>
        <meta charset=utf-8>
        <title>CKEditor Filecabinet</title>
        <link href="{SOURCE_HTTP}mod/filecabinet/templates/ckeditor/style.css" rel="stylesheet" type="text/css">
        <script>
            var current_folder_id = {CURRENT_FOLDER};
            var folder_open = '{SOURCE_HTTP}mod/filecabinet/templates/ckeditor/images/folder_open.png';
            var folder_closed = '{SOURCE_HTTP}mod/filecabinet/templates/ckeditor/images/directory.png';
            var folder_type = '{FOLDER_TYPE}';
            var authkey = '{AUTHKEY}';
            var autofloat = {AUTOFLOAT};
        </script>
        <script src="{SOURCE_HTTP}javascript/jquery/jquery.js" type="text/javascript"></script>
        <script src="{SOURCE_HTTP}javascript/jquery_ui/jquery-ui.js" type="text/javascript"></script>
        <script src="{SOURCE_HTTP}mod/filecabinet/templates/ckeditor/js/script.js"></script>
        <script src="{SOURCE_HTTP}mod/filecabinet/javascript/flowplayer/flowplayer-3.2.10.min.js"></script>
        <!-- BEGIN errors --><script>alert('{ERRORS}');</script> <!-- END errors -->
    </head>
    <body>
        <div id="folders">
            <!--
            <div id="file-type-buttons">
                <select id="folder-type">
                    <option value="1">Images</option>
                    <option value="2">Documents</option>
                    <option value="3">Multimedia</option>
                </select>
            </div>
            -->
            <div style="padding: 5px 0px; height : 30px; border-bottom : 1px solid black">
                <div style="text-align : center">{NEW_FOLDER}</div>
                <div id="new-folder-form" style="display : none"><input type="text" id="folder-name" name="folder_name" size="15" /><input id="submit-folder" type="button" value="Add" />
                </div>
            </div>
            <div id="tools">
                <div id="current-folder_listing">Current folder: <span style="font-weight:bold" id="current-folder"></span></div>
                <div id = "folder-form" style = "display : none;">
                    <iframe id="upload-frame" src="index.php?module=filecabinet&aop=ckuploadform&authkey={AUTHKEY}" frameborder="0" width="170px" height="150px"></iframe>
                </div>
            </div>
            <div id="folder-listing">
                {FOLDER_LISTING}
            </div>
        </div>
        <div id="files">
            {FILE_LISTING}
        </div>
    </body>
</html>