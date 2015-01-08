<div id="{ID}" class="flowplayer" href="mp4:{FILE_NAME}" style="width: {WIDTH}px;height:{HEIGHT}px">
</div>
<script type="text/javascript">
    $f("{ID}", "{source_http}javascript/flowplayer/flowplayer-3.2.18.swf", {
        clip: {
            provider: 'rtmp',
            autoPlay: false,
            showCaptions: true,
            scaling: 'orig'
        },
        plugins: {
            rtmp: {
                url: 'flowplayer.rtmp-3.2.13.swf',
                netConnectionUrl: '{FILE_DIRECTORY}'
            },
            captions: {
                url: 'flowplayer.captions-3.2.10.swf',
                captionTarget: 'content'
            },
            content: {
                url: "flowplayer.content-3.2.9.swf",
                bottom: '15%',
                width: '92%',
                height: '15%',
                backgroundColor: 'transparent',
                backgroundGradient: 'none',
                border: 0,
                // an outline is useful so that the subtitles are more visible
                textDecoration: 'outline',
                style: {
                    'body': {
                        fontSize: '15%',
                        fontFamily: 'Arial',
                        textAlign: 'center',
                        color: '#fff000'
                    }
                }
            }

        }
    });
</script>