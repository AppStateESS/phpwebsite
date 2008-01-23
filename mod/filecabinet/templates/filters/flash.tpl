<object type="application/x-shockwave-flash" data="templates/filecabinet/filters/flash/FlowPlayerLP.swf" width="{WIDTH}" height="{HEIGHT}" id="{ID}">
	<param name="allowScriptAccess" value="sameDomain" />
	<param name="movie" value="FlowPlayer.swf" />
	<param name="quality" value="high" />
	<param name="scale" value="noScale" />
	<param name="wmode" value="transparent" />
	<param name="flashvars" value="config={ 
        initialScale: 'scale',
        autoPlay: false, 
        autoBuffering : false,
        showLoopButton: false, 
        loop: false, 
        playList: [ <!-- BEGIN start-screen -->{ {START_SCREEN}, overlayId: 'play' },<!-- END start-screen -->
                    { url: '{FILE_PATH}' },
                    { overlayId: 'play' } ],
        }"
/>
</object>
