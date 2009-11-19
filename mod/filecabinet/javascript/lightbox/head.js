<style type="text/css">@import url("javascript/modules/filecabinet/lightbox/jquery.lightbox-0.5.css");</style>
<script type="text/javascript" src="javascript/modules/filecabinet/lightbox/jquery.lightbox-0.5.min.js"></script>

<script type="text/javascript">
$(function() {
    $('#gallery a').lightBox();
});
</script>

<script type="text/javascript">
$(function() {
   $('div.lightbox a').lightBox({
        imageLoading: 'javascript/modules/filecabinet/lightbox/loading.gif',
        imageBtnClose: 'javascript/modules/filecabinet/lightbox/close.gif',
        imageBtnPrev: 'javascript/modules/filecabinet/lightbox/prev.gif',
        imageBtnNext: 'javascript/modules/filecabinet/lightbox/next.gif',
        imageBlank: 'javascript/modules/filecabinet/lightbox/blank.gif',
        txtImage: '{txtImage}',
        txtOf: '{txtOf}'
    });
});
</script>
