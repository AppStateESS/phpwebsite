<style>
    .rtmp {
    background-color : white;
    border : 1px solid black;
	display:block;
	width:400px;
	height:300px;
	margin:25px 0;
	text-align:center;
}
</style>
    <a class="{ID} rtmp" href="{FILE_NAME}">
	<img width="400px" height="300px" src="{SOURCE_HTTP}mod/filecabinet/img/video_generic.jpg" />
</a>
<script type="text/javascript" src="{SOURCE_HTTP}javascript/flowplayer/flowplayer-3.2.6.min.js"></script>
<script type="text/javascript">
$f("a.{ID}", "{SOURCE_HTTP}javascript/flowplayer/flowplayer-3.2.7.swf", {
	clip: {provider: 'rtmp'},
	plugins: {
	  rtmp: {

		   // use latest RTMP plugin release
			url: '{SOURCE_HTTP}javascript/flowplayer/flowplayer.rtmp-3.2.3.swf',

			/*
				netConnectionUrl defines where the streams are found
			*/
			netConnectionUrl: '{FILE_DIRECTORY}'
	  }
	}
});
</script>