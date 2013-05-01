// get path of directory ckeditor
var basePath = CKEDITOR.basePath;
basePath = basePath.substr(0, basePath.indexOf("ckeditor/"));

//load external plugin
(function() {
    CKEDITOR.plugins.addExternal('filecabinet', basePath + 'ckeditor/phpws_plugins/filecabinet/', 'plugin.js');
})();


CKEDITOR.editorConfig = function(config)
{
    config.toolbar = 'MyToolbar';
    config.extraPlugins = 'filecabinet,autogrow,menubutton,scayt';
    config.scayt_autoStartup = true;
    config.scayt_sLang = 'en_US';
    config.removePlugins = 'resize';
    config.autoGrow_onStartup = true;
    config.autoGrow_maxHeight = 600;
    config.skin = 'kama';
    config.toolbar_MyToolbar =
            [
                ['Source', 'Maximize'],
                ['Cut', 'Copy', 'PasteText'],
                ['Undo', 'Redo', '-', 'Find', 'Replace', '-', 'SelectAll', 'RemoveFormat'],
                ['Filecabinet', 'Table', 'HorizontalRule', 'SpecialChar'],
                ['Link', 'Unlink', 'Anchor'],
                '/',
                ['Bold', 'Italic', 'Strike', '-', 'Subscript', 'Superscript'],
                ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', 'Blockquote'],
                ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'],
                ['Styles', 'Format']
            ];
};
