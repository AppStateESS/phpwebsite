<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Video player example</title>
        <meta content="text/html; charset=UTF-8"  http-equiv="Content-Type" />
        <script type="text/javascript" src="//matt.appstate.edu/phpwebsite/javascript/jquery/jquery-2.1.0.min.js"></script>
        <script type="text/javascript" src="//matt.appstate.edu/phpwebsite/javascript/flowplayer/flowplayer-3.2.13.min.js"></script>
        <style>
            .media {
                background-color : white;
                border : 1px solid black;
                display:block;
                width:400px;
                height:300px;
                margin:25px 0;
                text-align:center;
            }
        </style>
    </head>
    <body>
        <a id="sample1" class="media" href="mp4:breiner/DM_Authors.mp4"></a>
        <script type="text/javascript">
            $f("sample1", "flowplayer-3.2.18.swf", {
                clip: {provider: 'rtmp',
                    autoPlay: false,
                    showCaptions: false
                },
                plugins: {
                    rtmp: {
                        url: 'flowplayer.rtmp-3.2.13.swf',
                        netConnectionUrl: 'rtmp://streams.appstate.edu/vod/'
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
    </body>
</html>
