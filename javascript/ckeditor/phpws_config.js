// get path of directory ckeditor
var basePath = CKEDITOR.basePath;
basePath = basePath.substr(0, basePath.indexOf("ckeditor/"));

CKEDITOR.editorConfig = function (config)
{
    config.resize = true;
    config.toolbar = 'MyToolbar';
    config.skin = 'moono';
    config.extraPlugins = 'filecabinet';
    config.allowedContent = true;
    config.toolbar_MyToolbar =
            [
                ['Sourcedialog', 'Maximize'], ['PasteText', 'PasteFromWord'],
                ['Undo', 'Redo', '-', 'RemoveFormat'],
                ['Link', 'Unlink', 'Anchor'], ['Filecabinet', 'FontAwesome', 'Glyphicons', 'Youtube', 'Table'],
                ['HorizontalRule', 'SpecialChar'], ['Bold', 'Italic', '-', 'Subscript', 'Superscript', 'Format'],
                { name: 'texttransform', items: [ 'TransformTextToUppercase', 'TransformTextToLowercase', 'TransformTextCapitalize', 'TransformTextSwitcher' ] },
                ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', 'Blockquote'],
                ['JustifyLeft', 'JustifyCenter', 'JustifyRight']
            ];
};

CKEDITOR.dtd.$removeEmpty.span = 0;
CKEDITOR.dtd.$removeEmpty.i = 0;