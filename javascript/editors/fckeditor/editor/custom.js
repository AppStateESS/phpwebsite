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

FCKConfig.LinkBrowser = true;
FCKConfig.LinkBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Connector=connectors/phpws/connector.php';

FCKConfig.ImageBrowser = true;
FCKConfig.ImageBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Type=Image&Connector=connectors/phpws/connector.php';

FCKConfig.FileBrowser = true;
FCKConfig.FileBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Type=File&Connector=connectors/phpws/connector.php';

FCKConfig.FlashBrowser = true;
FCKConfig.FlashBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Type=Flash&Connector=connectors/phpws/connector.php';

FCKConfig.ImageUpload = true;
FCKConfig.ImageUploadURL = FCKConfig.BasePath + 'filemanager/upload/phpws/upload.php?Type=Image';
FCKConfig.ImageUploadAllowedExtensions	= ".(jpg|gif|jpeg|png)$" ;
FCKConfig.ImageUploadDeniedExtensions	= "" ;

FCKConfig.LinkUpload = true ;
FCKConfig.LinkUploadURL = FCKConfig.BasePath + 'filemanager/upload/phpws/upload.php?Type=File' ;
FCKConfig.LinkUploadAllowedExtensions	= "" ; // empty for all
FCKConfig.LinkUploadDeniedExtensions	= ".(php|php3|php5|phtml|asp|aspx|ascx|jsp|cfm|cfc|pl|bat|exe|dll|reg|cgi)$" ;	// empty for no one

FCKConfig.FlashUpload = true ;
FCKConfig.FlashUploadURL = FCKConfig.BasePath + 'filemanager/upload/phpws/upload.php?Type=Flash' ;
FCKConfig.FlashUploadAllowedExtensions	= ".(swf|fla)$" ;
FCKConfig.FlashUploadDeniedExtensions	= "" ; // empty for all

FCKConfig.SpellChecker = 'SpellerPages' ;
FCKConfig.StylesXmlPath		= 'phpwsstyles.xml' ;
FCKConfig.ProcessHTMLEntities	= false ;
FCKConfig.Plugins.Add( 'autogrow' ) ;
FCKConfig.FillEmptyBlocks	= false;