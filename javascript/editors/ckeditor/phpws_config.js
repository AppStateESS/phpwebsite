// get path of directory ckeditor
var basePath = CKEDITOR.basePath;
basePath = basePath.substr(0, basePath.indexOf("ckeditor/"));

//load external plugin
(function() {
    CKEDITOR.plugins.addExternal('filecabinet', basePath + 'ckeditor/phpws_plugins/filecabinet/', 'plugin.js');
})();

CKEDITOR.dtd.$removeEmpty.span = 0;

/**
 * to add aspell
 * 1) make sure aspell is installed on your server
 * 2) then add 'aspell' to config.extraPlugins
 * 3) add 'SpellCheck' to the config.toolbar_MyToolbar
 */
// to the toolbar

CKEDITOR.editorConfig = function(config)
{
    config.toolbar = 'MyToolbar';
    config.extraPlugins = 'filecabinet,menubutton,scayt,youtube,floatleft,floatright';
    config.scayt_autoStartup = true;
    config.scayt_sLang = 'en_US';
    config.removePlugins = 'resize';
    config.skin = 'kama';
    config.toolbar_MyToolbar =
            [
                ['Source', 'Maximize'],['Cut', 'Copy', 'PasteText', 'PasteFromWord'],
                ['Undo', 'Redo', '-', 'Find', 'Replace', '-', 'SelectAll', 'RemoveFormat'],
                ['Link', 'Unlink', 'Anchor'],['Filecabinet', 'floatleft', 'floatright', 'Youtube', 'Table'],
                ['HorizontalRule', 'SpecialChar'],['Bold', 'Italic', 'Strike', '-', 'Subscript', 'Superscript','Format'],
                ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', 'Blockquote'],
                ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']
            ];
};
