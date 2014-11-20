<html>
    <head>
        <style type="text/css">
            body {
                padding-top : 1em;
            }
            body, input {
                font-size : 10px;
            }
            body, form {
                margin : 0px;
            }
            #thumbnail img{
                width:80px;
                height:80px;
            }

            #upload-area {
                border:1px solid #e3e3e3;
                text-align: center;
                padding:10px;
                cursor:pointer;
                height : 100px;
            }
        </style>
        <script type="text/javascript" src="{SOURCE_HTTP}javascript/jquery/jquery.js"></script>
        <script src="{SOURCE_HTTP}mod/filecabinet/templates/ckeditor/js/thumb.js"></script>
        <!-- BEGIN query -->
        <script type="text/javascript">
            $(function () {
                window.parent.setCurrentFolderId({FOLDER_ID});
                window.parent.initScript();
            });
        </script>
        <!-- END query -->
        <title>Upload form</title>
    </head>
    <body>
        {START_FORM}
        <input type="file" name="filename" id="upload_filename" style="display:none" multiple="multiple" >
        <div style="text-align:center">{SUBMIT}</div>
        <div id="upload-area">Click here to select file...
            <div id="thumbnail"></div>
        </div>
        {END_FORM}
    </body>
</html>