<div id="{ID}">
		<strong>You need to upgrade your Flash Player</strong>
		This is replaced by the Flash content. 
		Place your alternate content here and users without the Flash plugin or with 
		Javascript turned off will see this. Content here allows you to leave out <code>noscript</code> 
		tags. Include a link to <a href="swfobject.html?detectflash=false">bypass the detection</a> if you wish.
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
                so.addParam("flashvars", "config={initialScale: 'scale',autoPlay: false, autoBuffering : false,showLoopButton: false, loop: false, showPlayListButtons: false, playList: [{ url: '{START_SCREEN}' },{ url: '{VIDEO_PATH}' }]}");
		so.write("{ID}");
		
		// ]]>
</script>
