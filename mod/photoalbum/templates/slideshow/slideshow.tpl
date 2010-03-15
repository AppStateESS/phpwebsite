<form name="slideshowForm" id="slideshowForm"><span
    class="smalltext"
>{QUIT_SLIDESHOW}</span> &nbsp;&nbsp;<span class="smalltext">|</span>&nbsp;&nbsp;
<span style="font-weight: bold;"> <a href='#'
    onclick='startStop(); return false;' id='sscontrol'
> <span id="startstop" class="smalltext">{STOP_TEXT}</span></a> </span>
&nbsp;&nbsp;<span class="smalltext">|</span>&nbsp;&nbsp; <span
    class="smalltext" style="font-weight: bold;"
>{ADJUST_SPEED_LABEL}</span> {ADJUST_SPEED_TEXT_FIELD} <span
    id="filtercontrol"
> <span id="ieFilterLabel" class="smalltext"></span> <span
    id="ieFilterFieldTpl"
></span> </span> &nbsp;&nbsp;<span class="smalltext">|</span>&nbsp;&nbsp; <span
    style="font-weight: bold;" class="smalltext"
>{LOOP_LABEL}</span> <input type="checkbox" id="loop" name="loop" value="1"
    onclick="changeLoop();"
/>
<div style="padding-right: 20px; text-align: right;" id="indexcontrol">
<p id="imageIndexInfo" class="smalltext"></p>
</div>
<table border="1" width="100%" class="bg_medium">
    <tr>
        <td>
        <table align="center" width="100%" height="{LARGEST_IMHEIGHT}px"
            class="bg_light"
        >
            <tr>
                <td><br />
                <i>
                <p id="textcontrol" align="center"
                    style="font-size: larger;"
                ><span id="imageNameText">{DEFAULT_TITLE}</span></p>
                </i></td>
            </tr>
            <tr>
                <td>
                <p align="center"><!-- BEGIN MAIN_IMAGE --> <img
                    src="{FIRST_IMAGE}" id="slide" name="slide"
                    border="0"
                /> {IMAGE} <!-- END MAIN_IMAGE --></p>
                </td>
            </tr>
            <tr>
                <td>
                <p id="blurbcontrol" align="center"><span
                    id="imageBlurbText"
                ></span></p>
                <br />
                <br />
                &nbsp;</td>
            </tr>
        </table>
        </td>
    </tr>
</table>
</form>
<p align="right">{LOW_TECH_LINK}</p>
<script type="text/javascript" language="JavaScript">
//<![CDATA[

init();

//]]>
</script>
