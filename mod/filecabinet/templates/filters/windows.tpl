<OBJECT id='mediaPlayer' width="{WIDTH}" height="{HEIGHT}" classid='CLSID:22d6f312-b0f6-11d0-94ab-0080c74c7e95' 
      codebase='http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=5,1,52,701'
      standby='Loading Microsoft Windows Media Player components...' type='application/x-oleobject'>
    <param name='fileName' value="{VIDEO_PATH}">
    <param name='animationatStart' value='false'>
    <param name='transparentatStart' value='true'>
    <param name='autoStart' value="false">
    <param name='showControls' value="true">
    <param name='loop' value="false">
    <EMBED type='application/x-mplayer2'
        pluginspage='http://microsoft.com/windows/mediaplayer/en/download/'
        id='mediaPlayer' name='mediaPlayer' displaysize='4' autosize='-1' 
        bgcolor='darkblue' showcontrols="true" showtracker='-1' 
        showdisplay='0' showstatusbar='-1' videoborder3d='-1' width="{WIDTH}" height="{HEIGHT}"
        src="{VIDEO_PATH}" autostart="true" designtimesp='5311' loop="true">
    </EMBED>
</OBJECT>
