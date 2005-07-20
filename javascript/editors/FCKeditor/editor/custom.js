// Spellcheck needs configuration so removed
// Form line removed because this is not the place for it
// Removing 'FontFormat','FontName','FontSize' 'TextColor','BGColor',
// and 'Style' as they interfere with the cursor keys.
// Removed 'Image' as well, may put it back later


FCKConfig.ToolbarSets["phpws"] = [
	['NewPage','Preview'],
	['Cut','Copy','Paste','PasteText','PasteWord'],
	['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
	'/',
	['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript'],
	['OrderedList','UnorderedList','-','Outdent','Indent'],
	['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
	'/',
	['Table', 'Link','Unlink','Anchor'],
	['Rule','Smiley','SpecialChar', '-', 'Source']
] ;

FCKConfig.LinkBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Connector=connectors/php/connector.php' ;
FCKConfig.ImageBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Type=Image&Connector=connectors/php/connector.php' ;
