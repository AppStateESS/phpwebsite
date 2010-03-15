<form name="slideshowForm">
<table width="100%">
    <tr>
        <td width="15%" align="center"><!-- BEGIN PREVIOUS_LINK -->
        <span class="smalltext">{PREVIOUS}</span> <!-- END PREVIOUS_LINK -->
        </td>
        <td width="15%" align="center"><span class="smalltext">{QUIT_SLIDESHOW}</span></td>
        <td align="right">{IMAGE_INDEX_INFO}</td>
        <td width="15%" align="center"><!-- BEGIN NEXT_LINK --> <span
            class="smalltext"
        >{NEXT}</span> <!-- END NEXT_LINK --></td>
    </tr>
</table>
<br />
<table border="1" width="100%" class="bg_medium">
    <tr>
        <td>
        <table align="center" width="100%" height="{LARGEST_IMHEIGHT}px"
            class="bg_light"
        >
            <tr>
                <td><br />
                <i>
                <p id="imageNameText" align="center"
                    style="font-size: larger;"
                >{IMAGE_NAME}</p>
                </i></td>
            </tr>
            <tr>
                <td>
                <p align="center"><img width="{IMAGE_WIDTH}"
                    height="{IMAGE_HEIGHT}" src="{IMAGE_SRC}"
                    name="slide" border="0"
                /></p>
                </td>
            </tr>
            <tr>
                <td>
                <p id="imageBlurbText" align="center">{IMAGE_BLURB}</p>
                </td>
            </tr>
        </table>
        </td>
    </tr>
</table>
<!-- BEGIN HIGH_TECH_LINK -->
<p align="right">{HIGH_TECH_LINK}</p>
<!-- END HIGH_TECH_LINK --></form>
