$(window).load(function() {
    if (typeof CKEDITOR !== 'undefined')
    {
        CKEDITOR.config.contentsCss = '{THEME_HTTP}dist/css/bootstrap.min.css';
    }
    $('.dropdown-toggle').dropdown();
});
