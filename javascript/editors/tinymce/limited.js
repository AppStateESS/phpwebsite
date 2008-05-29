tinyMCE.init({
	theme : 'advanced',
	mode : 'specific_textareas',
	plugins : 'bbcode,emotions',
	theme_advanced_buttons1 : 'bold,italic,underline,undo,redo,link,unlink,image,forecolor,emotions,styleselect,removeformat',
	theme_advanced_buttons2 : '',
	theme_advanced_buttons3 : '',
	theme_advanced_toolbar_location : 'bottom',
	theme_advanced_toolbar_align : 'center',
	theme_advanced_styles : 'Code=codeStyle;Quote=quoteStyle',
	content_css : 'bbcode.css',
	entity_encoding : 'raw',
	add_unload_trigger : false,
	remove_linebreaks : false
});
