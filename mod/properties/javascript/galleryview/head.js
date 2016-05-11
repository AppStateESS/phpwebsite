<script type="text/javascript">jQuery.browser={};(function(){jQuery.browser.msie=false;jQuery.browser.version=0;if(navigator.userAgent.match(/MSIE ([0-9]+)\./)){jQuery.browser.msie=true;jQuery.browser.version=RegExp.$1;}})();</script>
<script type="text/javascript" src="{source_http}mod/properties/javascript/galleryview/jquery.easing.1.3.js"></script>
<script type="text/javascript" src="{source_http}mod/properties/javascript/galleryview/jquery.galleryview-2.1.1-pack.js"></script>
<script type="text/javascript" src="{source_http}mod/properties/javascript/galleryview/jquery.timers-1.2.js"></script>
<style type="text/css">@import url("{source_http}javascript/lightbox/jquery.lightbox-0.5.css");</style>
<script type="text/javascript" src="{source_http}javascript/lightbox/jquery.lightbox-0.5.min.js"></script>
<script type="text/javascript" src="{source_http}javascript/lightbox/script.js"></script>
<link rel="stylesheet"  href="{source_http}mod/properties/javascript/galleryview/galleryview.css" type="text/css" />
<style type="text/css">
#jquery-overlay {z-index : 1000;}
#jquery-lightbox {z-index : 1010;}
.panel_content img {text-align : center;}
</style>
<script type="text/javascript">
var panel_width = {panel_width};
var panel_height = {panel_height};
var lbImageLoading = '{source_http}javascript/lightbox/loading.gif';
var lbImageBtnClose = '{source_http}javascript/lightbox/close.gif';
var lbImageBtnPrev = '{source_http}javascript/lightbox/prev.gif';
var lbImageBtnNext = '{source_http}javascript/lightbox/next.gif';
var lbImageBlank = '{source_http}javascript/lightbox/blank.gif';

$(document).ready(function(){
    $('#gallery').galleryView({
        panel_width: panel_width,
        panel_height: panel_height,
        frame_width: 80,
        frame_height: 60,
        pause_on_hover: true,
        transition_interval : 20000,
        nav_theme : 'light',
        easing: 'easeInOutQuad',
        background_color: 'transparent',
        border : 'none'

    });

    $('#gallery a.lightbox').lightBox({
        imageLoading: lbImageLoading,
        imageBtnClose: lbImageBtnClose,
        imageBtnPrev: lbImageBtnPrev,
        imageBtnNext: lbImageBtnNext,
        imageBlank: lbImageBlank
    })
});
</script>
