// Spellcheck needs configuration so removed
// Form line removed because this is not the place for it

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
	['TextColor','BGColor'],
	['Image','Table','Rule','Smiley','SpecialChar'],
	['Style','FontFormat','FontName'],
	['FontSize', '-', 'Source', 'About']
] ;

FCKConfig.LinkBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Connector=connectors/php/connector.php' ;
FCKConfig.ImageBrowserURL = FCKConfig.BasePath + 'filemanager/browser/default/browser.html?Type=Image&Connector=connectors/php/connector.php' ;
