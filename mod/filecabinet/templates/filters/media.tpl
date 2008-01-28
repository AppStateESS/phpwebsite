<script type="text/javascript" src="templates/filecabinet/filters/media/swfobject.js"></script>

<div id="{ID}"><a href="http://www.macromedia.com/go/getflashplayer">Get the Flash Player</a> to see this player.</div>

<script type="text/javascript">
var so = new SWFObject('templates/filecabinet/filters/media/mediaplayer.swf','mpl{ID}','{WIDTH}','{HEIGHT}','8');
so.addParam('allowscriptaccess','always');
so.addParam('allowfullscreen','true');
so.addVariable('width','{WIDTH}');
so.addVariable('height','{HEIGHT}');
<!-- BEGIN display-width -->so.addVariable('displayheight','{DISPLAYHEIGHT}');<!-- END display-width -->
<!-- BEGIN thumbnail -->so.addVariable('image','{THUMBNAIL}');<!-- END thumbnail -->
so.addVariable('file','{FILE_PATH}');
so.write('{ID}');
</script>
