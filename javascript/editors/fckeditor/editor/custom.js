// Form line removed because this is not the place for it
// Removed 'FontFormat','FontName','FontSize', 'TextColor', and 'BGColor'
// because they lead to garish markup.


FCKConfig.ToolbarSets["phpws"] = [
	['FitWindow', 'NewPage','Preview', 'Source'],
	['Cut','Copy','Paste','PasteText','PasteWord'],
	['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
	'/',
	['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript'],
	['OrderedList','UnorderedList','-', 'Blockquote', 'Outdent','Indent'],
	['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
	'/',
	['Image', 'Flash', 'Table', 'Link','Unlink','Anchor'],
	['SpellCheck', 'Rule','Smiley','SpecialChar', '-', 
	'Style', 'FontFormat', 'filecabinet']
] ;

/**
 * Paste the below into the ToolbarSets above to "expand" your choices
 *

, '/',
['FontName','FontSize', 'TextColor','BGColor']

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

FCKConfig.SpellChecker = 'WSC' ; //'WSC' | 'SpellerPages' | 'ieSpell'
FCKConfig.IeSpellDownloadUrl	= 'http://www.iespell.com/download.php' ;
FCKConfig.StylesXmlPath		= 'phpwsstyles.xml' ;
FCKConfig.ProcessHTMLEntities	= false ;
// Not working properly in current versions. Disabled for now.
//FCKConfig.Plugins.Add( 'autogrow' ) ;
FCKConfig.FillEmptyBlocks	= false;


FCKConfig.Plugins.Add( 'filecabinet' ) ;