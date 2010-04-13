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
	['filecabinet', 'Image', 'YouTube', 'Table', 'Link','Unlink','Anchor'],
	['SpellCheck', 'Rule','Smiley','SpecialChar', 'TextColor','BGColor'], '/',
	['Style', 'FontFormat', 'FontName','FontSize']
] ;

FCKConfig.LinkBrowser = false ;

FCKConfig.ImageBrowser = true ;
FCKConfig.ImageBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Type=Image&Connector=' + encodeURIComponent( FCKConfig.BasePath + 'filemanager/connectors/phpws/connector.php' ) ;
FCKConfig.ImageBrowserWindowWidth  = FCKConfig.ScreenWidth * 0.7 ;	// 70% ;
FCKConfig.ImageBrowserWindowHeight = FCKConfig.ScreenHeight * 0.7 ;	// 70% ;

FCKConfig.FlashBrowser = true ;
FCKConfig.FlashBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Type=Flash&Connector=' + encodeURIComponent( FCKConfig.BasePath + 'filemanager/connectors/phpws/connector.php' ) ;
FCKConfig.FlashBrowserWindowWidth  = FCKConfig.ScreenWidth * 0.7 ;	//70% ;
FCKConfig.FlashBrowserWindowHeight = FCKConfig.ScreenHeight * 0.7 ;	//70% ;

FCKConfig.LinkUpload = false ;


FCKConfig.ImageUpload = true ;
FCKConfig.ImageUploadURL = FCKConfig.BasePath + 'filemanager/connectors/phpws/upload.php?Type=Image' ;
FCKConfig.ImageUploadAllowedExtensions	= ".(jpg|gif|jpeg|png|bmp)$" ;		// empty for all
FCKConfig.ImageUploadDeniedExtensions	= "" ;							// empty for no one

FCKConfig.FlashUpload = true ;
FCKConfig.FlashUploadURL = FCKConfig.BasePath + 'filemanager/connectors/phpws/upload.php?Type=Flash' ;
FCKConfig.FlashUploadAllowedExtensions	= ".(swf|flv)$" ;		// empty for all
FCKConfig.FlashUploadDeniedExtensions	= "" ;

FCKConfig.SpellChecker = 'WSC' ; //'WSC' | 'SpellerPages' | 'ieSpell'
FCKConfig.IeSpellDownloadUrl	= 'http://www.iespell.com/download.php' ;
FCKConfig.StylesXmlPath		= 'phpwsstyles.xml' ;
FCKConfig.ProcessHTMLEntities	= false ;
FCKConfig.FillEmptyBlocks	= false;

// Not working properly in current versions. Disabled for now.
//FCKConfig.Plugins.Add( 'autogrow' ) ;
FCKConfig.Plugins.Add( 'filecabinet' ) ;
FCKConfig.Plugins.Add( 'youtube', 'en,ja') ;