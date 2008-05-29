tinyMCE.init({
        mode : "specific_textareas",
        strict_loading_mode : true,
        theme : 'advanced',
        spellchecker_rpc_url : 'javascript/editors/tinymce/jscripts/tiny_mce/plugins/spellchecker/rpc.php',
        textarea_trigger : "mceEditor",
        plugins : "safari,table,advimage,print,preview,searchreplace,spellchecker,fullscreen,paste",
        theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,fontsizeselect",
        theme_advanced_buttons2 : "undo,redo,|,table,bullist,numlist,outdent,indent,link,unlink,anchor,image,cleanup,preview",
        theme_advanced_buttons3 : "code hr,removeformat,visualaid,sub,sup,charmap,|,cut,copy,paste,pastetext,pasteword,|,print,fullscreen,spellchecker,search,replace"
});
