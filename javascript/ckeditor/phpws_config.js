// get path of directory ckeditor
var basePath = CKEDITOR.basePath;
basePath = basePath.substr(0, basePath.indexOf("ckeditor/"));

CKEDITOR.editorConfig = function (config)
{
    config.resize = true;
    config.toolbar = 'MyToolbar';
    config.skin = 'moono';
    config.extraPlugins = 'fontawesome,maximize,glyphicons,filecabinet';
    config.allowedContent = true;
    config.toolbar_MyToolbar =
            [
                ['Source', 'Maximize'], ['Cut', 'Copy', 'PasteText', 'PasteFromWord'],
                ['Undo', 'Redo', '-', 'RemoveFormat'],
                ['Link', 'Unlink', 'Anchor'], ['Filecabinet', 'FontAwesome', 'Glyphicons', 'Youtube', 'Table'],
                ['HorizontalRule', 'SpecialChar'], ['Bold', 'Italic', '-', 'Subscript', 'Superscript', 'Format'],
                ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', 'Blockquote'],
                ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']
            ];
};
