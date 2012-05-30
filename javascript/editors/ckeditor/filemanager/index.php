<?php

$sn = & $_GET['sn'];
session_name($sn);
session_start();
$ck_image_dir = $_SESSION['ck_dir'];
$ck_image_http = $_SESSION['ck_http'];
$home_dir = $_SESSION['home_dir'];
$source_http = $_SESSION['source_http'];
echo <<<EOF
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>File Manager</title>
<link rel="stylesheet" type="text/css" href="{$source_http}styles/reset.css" />
<link rel="stylesheet" type="text/css" href="{$source_http}scripts/jquery.filetree/jqueryFileTree.css" />
<link rel="stylesheet" type="text/css" href="{$source_http}scripts/jquery.contextmenu/jquery.contextMenu-1.01.css" />
<link rel="stylesheet" type="text/css" href="{$source_http}styles/filemanager.css" />
<!--[if IE]>
<link rel="stylesheet" type="text/css" href="styles/ie.css" />
<![endif]-->
<script type="text/javascript">
var sn = '$sn'; var fileRoot = '$ck_image_dir';var showThumbs = true; var lang = 'php'; var home_dir = '$home_dir';var ck_image_dir = '$ck_image_dir';
            var ck_image_http = '$ck_image_http'; var source_http= '$source_http';
</script>
</head>
<body>
<div>
<form id="uploader" method="post">
<button id="home" name="home" type="button" value="Home">&nbsp;</button>
<h1></h1>
<div id="uploadresponse"></div>
<input id="mode" name="mode" type="hidden" value="add" />
<input id="currentpath" name="currentpath" type="hidden" />
<input	id="newfile" name="newfile" type="file" />
<button id="upload" name="upload" type="submit" value="Upload"></button>
<button id="newfolder" name="newfolder" type="button" value="New Folder"></button>
<button id="grid" class="ON" type="button">&nbsp;</button>
<button id="list" type="button">&nbsp;</button>
</form>
<div id="splitter">
<div id="filetree"></div>
<div id="fileinfo">
<h1></h1>
</div>
</div>

<ul id="itemOptions" class="contextMenu">
	<li class="select"><a href="#select"></a></li>
	<li class="download"><a href="#download"></a></li>
	<li class="rename"><a href="#rename"></a></li>
	<li class="delete separator"><a href="#delete"></a></li>
</ul>

<script type="text/javascript" src="{$source_http}scripts/jquery-1.6.1.min.js"></script>
<script type="text/javascript" src="{$source_http}scripts/jquery.form-2.63.js"></script>
<script type="text/javascript" src="{$source_http}scripts/jquery.splitter/jquery.splitter-1.5.1.js"></script>
<script type="text/javascript" src="{$source_http}scripts/jquery.filetree/jqueryFileTree.js"></script>
<script type="text/javascript" src="{$source_http}scripts/jquery.contextmenu/jquery.contextMenu-1.01.js"></script>
<script type="text/javascript" src="{$source_http}scripts/jquery.impromptu-3.1.min.js"></script>
<script type="text/javascript" src="{$source_http}scripts/jquery.tablesorter-2.0.5b.min.js"></script>
<script type="text/javascript" src="{$source_http}scripts/filemanager.config.js"></script>
<script type="text/javascript" src="{$source_http}scripts/filemanager.js"></script></div>
</body>
</html>
EOF;
?>