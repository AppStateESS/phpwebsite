// get path of directory ckeditor
var basePath = CKEDITOR.basePath;
basePath = basePath.substr(0, basePath.indexOf("ckeditor/"));

CKEDITOR.editorConfig = function (config)
{
    config.resize = true;
    config.skin = 'moono';
    config.extraPlugins = 'fc_document,fc_image,save';
    config.allowedContent = true;
    config.removeButtons = 'Underline,Cut,Copy,Iframe,About,Styles,Paste,Image';
    config.removePlugins = 'maxheight';
    config.height = '400';
    config.format_tags = 'p;h3;h4;h5;h6';

    config.toolbarGroups = [
        {name: 'document', groups: ['mode', 'document', 'doctools']},
        {name: 'tools'},
        {name: 'clipboard', groups: ['clipboard', 'undo']},
        {name: 'editing', groups: ['find', 'selection', 'spellchecker']},
        {name: 'links'},
        {name: 'insert'},
        {name: 'others'},
        {name: 'basicstyles', groups: ['basicstyles', 'cleanup']},
        {name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align', 'bidi']},
        {name: 'styles'}
    ];

    config.codemirror = {
        // Set this to the theme you wish to use (codemirror themes)
        theme: 'default',
        // Whether or not you want to show line numbers
        lineNumbers: true,
        // Whether or not you want to use line wrapping
        lineWrapping: true,
        // Whether or not you want to highlight matching braces
        matchBrackets: true,
        // Whether or not you want tags to automatically close themselves
        autoCloseTags: true,
        // Whether or not you want Brackets to automatically close themselves
        autoCloseBrackets: true,
        // Whether or not to enable search tools, CTRL+F (Find), CTRL+SHIFT+F (Replace), CTRL+SHIFT+R (Replace All), CTRL+G (Find Next), CTRL+SHIFT+G (Find Previous)
        enableSearchTools: true,
        // Whether or not you wish to enable code folding (requires 'lineNumbers' to be set to 'true')
        enableCodeFolding: true,
        // Whether or not to enable code formatting
        enableCodeFormatting: true,
        // Whether or not to automatically format code should be done when the editor is loaded
        autoFormatOnStart: true,
        // Whether or not to automatically format code should be done every time the source view is opened
        autoFormatOnModeChange: true,
        // Whether or not to automatically format code which has just been uncommented
        autoFormatOnUncomment: true,
        // Whether or not to highlight the currently active line
        highlightActiveLine: true,
        // Define the language specific mode 'htmlmixed' for html including (css, xml, javascript), 'application/x-httpd-php' for php mode including html, or 'text/javascript' for using java script only
        mode: 'htmlmixed',
        // Whether or not to show the search Code button on the toolbar
        showSearchButton: true,
        // Whether or not to show Trailing Spaces
        showTrailingSpace: true,
        // Whether or not to highlight all matches of current word/selection
        highlightMatches: true,
        // Whether or not to show the format button on the toolbar
        showFormatButton: true,
        // Whether or not to show the comment button on the toolbar
        showCommentButton: false,
        // Whether or not to show the uncomment button on the toolbar
        showUncommentButton: false,
        // Whether or not to show the showAutoCompleteButton button on the toolbar
        showAutoCompleteButton: true

    };
};

CKEDITOR.dtd.$removeEmpty.span = 0;
CKEDITOR.dtd.$removeEmpty.i = 0;