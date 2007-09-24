<div id="{ID}">
<strong>You need to upgrade your Flash Player</strong>
</div>

<script type="text/javascript">
		// <![CDATA[
		var so = new SWFObject("templates/filecabinet/filters/flash/FlowPlayerLP.swf", "FlowPlayerLP", "{WIDTH}", "{HEIGHT}", "8", "#ffffff");
                // this line is optional, but this example uses the variable and displays this text inside the flash movie
                so.addParam("allowScriptAccess", "sameDomain");
                so.addParam("movie", "FlowPlayerLP.swf");
                so.addParam("quality", "high");
                so.addParam("scale", "noScale");
                so.addParam("wmode", "transparent");
                so.addParam("flashvars", "config={initialScale: 'scale',autoPlay: false, autoBuffering : false,showLoopButton: false, loop: false, showPlayListButtons: false, playList: [{ {START_SCREEN} },{ url: '{FILE_PATH}' }]}");
		so.write("{ID}");
		
		// ]]>
</script>
