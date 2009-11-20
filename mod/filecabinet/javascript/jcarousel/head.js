<style type="text/css">@import url("javascript/modules/filecabinet/lightbox/jquery.lightbox-0.5.css");</style>
<script type="text/javascript" src="javascript/modules/filecabinet/lightbox/jquery.lightbox-0.5.min.js"></script>

<script type="text/javascript">
$(function() {
    $('#gallery a').lightBox();
});
</script>
<script type="text/javascript" src="javascript/modules/filecabinet/jcarousel/lib/jquery.jcarousel.pack.js"></script>

<link rel="stylesheet" type="text/css" href="javascript/modules/filecabinet/jcarousel/lib/jquery.jcarousel.css" />
<link rel="stylesheet" type="text/css" href="javascript/modules/filecabinet/jcarousel/skins/tango/skin.css" />


<style type="text/css">
#body-content .jcarousel-skin-tango .jcarousel-item {
    height:{HEIGHT}px;
    width:{WIDTH}px;
    margin-left : 0px;
}

#body-content .jcarousel-list {
    margin : 0px;
}

.jcarousel-skin-tango .jcarousel-clip-horizontal {
    height:{HEIGHT}px;
}

.jcarousel-skin-tango .jcarousel-container-horizontal {
}

.jcarousel-skin-tango .jcarousel-prev-horizontal,
.jcarousel-skin-tango .jcarousel-next-horizontal {
    top : {ARROW_POSITION}px;        
}

.jcarousel-skin-tango .jcarousel-clip-vertical {
    width:{WIDTH}px;
}

.jcarousel-skin-tango .jcarousel-container-vertical {
    width:{WIDTH}px;
}

.jcarousel-skin-tango .jcarousel-next-vertical,
.jcarousel-skin-tango .jcarousel-prev-vertical {
    left : {ARROW_POSITION}px;
}
</style>

<!-- BEGIN repeats -->
<style>
#{CARO_ID} .jcarousel-skin-tango .jcarousel-clip-horizontal,
#{CARO_ID} .jcarousel-skin-tango .jcarousel-container-horizontal {
    width:{TOTAL_SIZE}px;
}

#{CARO_ID} .jcarousel-skin-tango .jcarousel-clip-vertical,
#{CARO_ID} .jcarousel-skin-tango .jcarousel-container-vertical {
    height:{TOTAL_SIZE}px;
}
</style>
<script type="text/javascript">

jQuery(document).ready(function() {
    jQuery('#{CARO_ID} ul').jcarousel({
    vertical: {VERTICAL},
    scroll: {SCROLL}
    });
});
</script>
<!-- END repeats -->


<script type="text/javascript">
$(function() {
   $('div#caro-1 a').lightBox({
        imageLoading: 'javascript/modules/filecabinet/lightbox/loading.gif',
        imageBtnClose: 'javascript/modules/filecabinet/lightbox/close.gif',
        imageBtnPrev: 'javascript/modules/filecabinet/lightbox/prev.gif',
        imageBtnNext: 'javascript/modules/filecabinet/lightbox/next.gif',
        imageBlank: 'javascript/modules/filecabinet/lightbox/blank.gif',
        txtImage: 'Image',
        txtOf: 'of'
    });
   
   $('div#caro-2 a').lightBox({
       imageLoading: 'javascript/modules/filecabinet/lightbox/loading.gif',
       imageBtnClose: 'javascript/modules/filecabinet/lightbox/close.gif',
       imageBtnPrev: 'javascript/modules/filecabinet/lightbox/prev.gif',
       imageBtnNext: 'javascript/modules/filecabinet/lightbox/next.gif',
       imageBlank: 'javascript/modules/filecabinet/lightbox/blank.gif',
       txtImage: 'Image',
       txtOf: 'of'
   });
   
});
</script>
