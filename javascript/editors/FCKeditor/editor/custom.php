<?php

  /**
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

if (!empty($_REQUEST['module'])) {
    $module = preg_replace('/\W/', '', $_REQUEST['module']);
    $subdir = $module . '/';
    $connector = '?module=' . preg_replace('/\W/', '', $_REQUEST['module']);
} else {
    $module = null;
    $subdir = NULL;
    $connector = NULL;
}

$home_dir = substr($_SERVER['SCRIPT_FILENAME'], 0, strpos($_SERVER['SCRIPT_FILENAME'], 'javascript/editors/')) 
     . 'images/' . $subdir;

?>

// Form line removed because this is not the place for it
// Removed 'FontFormat','FontName','FontSize', 'TextColor', and 'BGColor'
// because they lead to garish markup.


FCKConfig.ToolbarSets["phpws"] = [
	['FitWindow', 'NewPage','Preview'],
	['Cut','Copy','Paste','PasteText','PasteWord'],
	['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
	'/',
	['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript'],
	['OrderedList','UnorderedList','-','Outdent','Indent'],
	['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
	'/',
	['Image', 'Flash', 'Table', 'Link','Unlink','Anchor'],
	['SpellCheck', 'Rule','Smiley','SpecialChar', '-', 'Source', 'Style']
] ;

/**
 * Paste the below into the ToolbarSets above to "expand" your choices
 *

, '/',
['FontFormat','FontName','FontSize', 'TextColor','BGColor', 'Style']

*
**/

server_path = '<?php echo $home_dir; ?>';

FCKConfig.LinkBroswer = true;
FCKConfig.LinkBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Connector=connectors/phpws/connector.php&ServerPath=' + server_path;

FCKConfig.ImageBrowser = true;
FCKConfig.ImageBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Type=Image&ServerPath=' + server_path + '&Connector=connectors/phpws/connector.php<?php echo $connector ?>';

FCKConfig.FlashBrowser = true;
FCKConfig.FlashBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Type=Flash&ServerPath=' + server_path + '&Connector=connectors/phpws/connector.php<?php echo $connector ?>';

FCKConfig.ImageUpload = true;
FCKConfig.ImageUploadURL = FCKConfig.BasePath + 'filemanager/upload/phpws/upload.php?Type=Image&module=<?php echo $module;?>';
FCKConfig.ImageUploadAllowedExtensions	= ".(jpg|gif|jpeg|png)$" ;
FCKConfig.ImageUploadDeniedExtensions	= "" ;


FCKConfig.SpellChecker = 'SpellerPages' ;
FCKConfig.StylesXmlPath		= FCKConfig.EditorPath + 'phpwsstyles.xml' ;

FCKConfig.ProcessHTMLEntities	= false ;