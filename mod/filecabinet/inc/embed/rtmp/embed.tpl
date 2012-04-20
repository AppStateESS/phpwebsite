<style>
    .{ID} {
    background-color : white;
    border : 1px solid black;
	display:block;
	width:{WIDTH}px;
	height:{HEIGHT}px;
	margin:25px 0;
	text-align:center;
}
</style>
    <a class="{ID}" href="{FILE_NAME}">
</a>
<script type="text/javascript" src="{SOURCE_HTTP}javascript/flowplayer/flowplayer-3.2.6.min.js"></script>
<script type="text/javascript">
$f("a.{ID}", "{SOURCE_HTTP}javascript/flowplayer/flowplayer-3.2.7.swf", {
	clip: {provider: 'rtmp', autoPlay : false},
	plugins: {
	  rtmp: {
			url: '{SOURCE_HTTP}javascript/flowplayer/flowplayer.rtmp-3.2.3.swf',
			netConnectionUrl: '{FILE_DIRECTORY}'
	  }
	}
});
</script>