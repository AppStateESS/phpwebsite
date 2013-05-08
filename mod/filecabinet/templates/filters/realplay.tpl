<table border="0" cellpadding="0" align="left">
    <!-- begin video window... -->
    <tr>
        <td><OBJECT id="rvocx"
            classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="320"
            height="240">
            <param name="src" value="{VIDEO_PATH}">
            <param name="autostart" value="false">
            <param name="controls" value="imagewindow">
            <param name="console" value="video">
            <param name="loop" value="false">
            <EMBED src="{VIDEO_PATH}" width="{WIDTH}" height="{HEIGHT}"
                loop="false" type="audio/x-pn-realaudio-plugin"
                controls="imagewindow" console="video" autostart="false">
            </EMBED> </OBJECT></td>
    </tr>
    <!-- ...end video window -->
    <!-- begin control panel... -->
    <tr>
        <td><OBJECT id="rvocx"
            classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="{WIDTH}"
            height="30">
            <param name="src" value="{VIDEO_PATH}">
            <param name="autostart" value="false">
            <param name="controls" value="ControlPanel">
            <param name="console" value="video">
            <EMBED src="http://servername/path/to/media.file" width="{WIDTH}"
                height="30" controls="ControlPanel"
                type="audio/x-pn-realaudio-plugin" console="video"
                autostart="false">
            </EMBED> </OBJECT></td>
    </tr>
    <!-- ...end control panel -->
    <!-- ...end embedded RealMedia file -->
    <!-- begin link to launch external media player... -->
    <tr>
        <td align="center"><a href="http://servername/path/to/media.file"
            style="font-size: 85%;" target="_blank">Launch in
        external player</a> <!-- ...end link to launch external media player... -->
        </td>
    </tr>
</table>
