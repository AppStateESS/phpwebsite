<html>
    <head>
        <style type="text/css">
            body, input {
                font-size : 10px;
            }
            body, form {
                margin : 0px;
            }
        </style>
        <script type="text/javascript" src="{SOURCE_HTTP}javascript/jquery/jquery.js"></script>
        <!-- BEGIN query -->
        <script type="text/javascript">
            $(function() {
               window.parent.setCurrentFolderId({FOLDER_ID});
               window.parent.initScript();
            });
        </script>
        <!-- END query -->
        <title>Upload form</title>
    </head>
    <body>
        {START_FORM}
        {FILENAME}
        {SUBMIT}
        {END_FORM}
    </body>
</html>