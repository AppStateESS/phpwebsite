<link rel="stylesheet" type="text/css" href="javascript/modules/filecabinet/lightbox/jquery.lightbox-0.5.css" />
<link rel="stylesheet" type="text/css" href="javascript/modules/filecabinet/jcarousel/lib/jquery.jcarousel.css" />
<link rel="stylesheet" type="text/css" href="javascript/modules/filecabinet/jcarousel/skins/tango/skin.css" />
<script type="text/javascript" src="javascript/modules/filecabinet/lightbox/jquery.lightbox-0.5.min.js"></script>
<script type="text/javascript" src="javascript/modules/filecabinet/jcarousel/lib/jquery.jcarousel.pack.js"></script>

<style type="text/css">
.jcarousel-skin-tango .jcarousel-item {height:{HEIGHT}px !important;width:{WIDTH}px !important;margin-left:0px !important;}
.jcarousel-skin-tango .jcarousel-clip-horizontal {height:{HEIGHT}px;}
.jcarousel-skin-tango .jcarousel-prev-horizontal,.jcarousel-skin-tango .jcarousel-next-horizontal{top:{ARROW_POSITION}px;}
.jcarousel-skin-tango .jcarousel-clip-vertical,.jcarousel-skin-tango .jcarousel-container-vertical {width:{WIDTH}px;}
.jcarousel-skin-tango .jcarousel-next-vertical,.jcarousel-skin-tango .jcarousel-prev-vertical {left:{ARROW_POSITION}px;}
</style>
<script type="text/javascript">
var image = '{IMAGE}';
var of = '{OF}';
</script>
<script type="text/javascript" src="javascript/modules/filecabinet/jcarousel/script.js"></script>

<!-- BEGIN repeats -->
<script type="text/javascript">
jQuery(document).ready(function() {
    carousel('{CARO_ID}', {VERTICAL}, {SCROLL}, {TOTAL_SIZE});
});
</script>
<!-- END repeats -->
