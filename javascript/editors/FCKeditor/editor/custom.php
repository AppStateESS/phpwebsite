<?php

if (!empty($_REQUEST['module'])) {
    $subdir = preg_replace('/\W/', '', $_REQUEST['module']) . '/';
    $connector = '?module=' . preg_replace('/\W/', '', $_REQUEST['module']);
} else {
    $subdir = NULL;
    $connector = NULL;
}


?>

// Spellcheck needs configuration so removed
// Form line removed because this is not the place for it
// Removing 'FontFormat','FontName','FontSize' 'TextColor','BGColor',
// and 'Style' as they interfere with the cursor keys.


FCKConfig.ToolbarSets["phpws"] = [
	['NewPage','Preview'],
	['Cut','Copy','Paste','PasteText','PasteWord'],
	['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
	'/',
	['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript'],
	['OrderedList','UnorderedList','-','Outdent','Indent'],
	['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
	'/',
	['Image', 'Table', 'Link','Unlink','Anchor'],
	['Rule','Smiley','SpecialChar', '-', 'Source']
] ;

server_path = '../../../../../../../../../images/<?php echo $subdir ?>';

FCKConfig.LinkBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Connector=connectors/phpws/connector.php' ;
//FCKConfig.ImageBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Connector=connectors/phpws/connector.php&Type=Image&ServerPath=' + server_path ;
FCKConfig.ImageBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Type=Image&ServerPath=' + server_path + '&Connector=connectors/phpws/connector.php<?php echo $connector ?>';
