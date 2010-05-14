<?php
$sn = & $_GET['sn'];
session_name($sn);
session_start();
$ck_image_dir = $_SESSION['ck_dir'];
$ck_image_http = $_SESSION['ck_http'];
$source_dir = $_SESSION['source_dir'];

echo <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <title>File Manager</title>
        <link rel="stylesheet" type="text/css" href="styles/reset.css" />
        <link rel="stylesheet" type="text/css" href="scripts/jquery.filetree/jqueryFileTree.css" />
        <link rel="stylesheet" type="text/css" href="scripts/jquery.contextmenu/jquery.contextMenu.css" />
        <link rel="stylesheet" type="text/css" href="styles/filemanager.css" />
        <!--[if IE]>
        <link rel="stylesheet" type="text/css" href="styles/ie.css" />
        <![endif]-->

        <script type="text/javascript">
            var sn = '$sn';
            var fileRoot = '$ck_image_dir';
            var showThumbs = true;
            var lang = 'phpws';
            var source_dir = '$source_dir';
        </script>
        <script type="text/javascript" src="scripts/jquery-1.2.6.min.js"></script>
        <script type="text/javascript" src="scripts/jquery.form.js"></script>
        <script type="text/javascript" src="scripts/jquery.splitter/jquery.splitter.js"></script>
        <script type="text/javascript" src="scripts/jquery.contextmenu/jquery.contextMenu.js"></script>
        <script type="text/javascript" src="scripts/jquery.impromptu-1.5.js"></script>
        <script type="text/javascript" src="scripts/jquery.tablesorter.min.js"></script>
        <script type="text/javascript" src="scripts/jquery.filetree/pwsjqueryFileTree.js"></script>
        <script type="text/javascript" src="scripts/phpws_filemanager.js"></script>
    </head>
    <body>
        <form id="uploader" method="post">
            <h1></h1>
            <div id="uploadresponse"></div>
            <input id="mode" name="mode" type="hidden" value="add" />
            <input id="currentpath" name="currentpath" type="hidden" />
            <input id="newfile" name="newfile" type="file" />
            <button id="upload" name="upload" type="submit" value="Upload">Upload</button>
            <button id="newfolder" name="newfolder" type="button" value="New Folder">New Folder</button>
            <button id="grid" class="ON" type="button" title="Switch to grid view.">&nbsp;</button><button id="list" type="button" title="Switch to list view.">&nbsp;</button>
        </form>
        <div id="splitter">
            <div id="filetree"></div>
            <div id="fileinfo"><h1>Select an item from the left.</h1></div>
        </div>

        <ul id="itemOptions" class="contextMenu">
            <li class="select"><a href="#select">Select</a></li>
            <li class="download"><a href="#download">Download</a></li>
            <li class="rename"><a href="#rename">Rename</a></li>
            <li class="delete separator"><a href="#delete">Delete</a></li>
        </ul>
    </body>
</html>
EOF;
?>