<div id="slot-no">#{SORT}</div>
{START_FORM}
<p>{SHOW_THUMBNAILS} {SHOW_THUMBNAILS_LABEL}</p>
<table cellpadding="4" width="100%">
    <tr>
        <td>{BG_WIDTH_LABEL} {BG_WIDTH}&#160;&#160;&#160;&#160;
        {BG_HEIGHT_LABEL} {BG_HEIGHT}</td>
    </tr>
    <tr>
        <td>{TN_WIDTH_LABEL} {TN_WIDTH}&#160;&#160;&#160;&#160;{TN_HEIGHT_LABEL} {TN_HEIGHT}</td>
    </tr>
</table>
<p style="font-size : .9em">Changing above requires uploading of new images.</p>
<p>{BACKGROUND_IMAGE_LABEL}<br />
    {BACKGROUND_IMAGE} {pic_dimensions}</p>
<p>{THUMBNAIL_IMAGE_LABEL}<br />
    {THUMBNAIL_IMAGE} {thumb_dimensions}</p>
<p>{THUMBNAIL_TEXT_LABEL}<br />{THUMBNAIL_TEXT}</p>
<p>{FEATURE_TEXT_LABEL}<br />
    {FEATURE_TEXT}
</p>
<p>{DESTINATION_URL_LABEL}<br />{DESTINATION_URL}</p>
<p>{FEATURE_X_LABEL} {FEATURE_X} {FEATURE_Y_LABEL} {FEATURE_Y}</p>
<p>{F_WIDTH_LABEL} {F_WIDTH} {F_HEIGHT_LABEL} {F_HEIGHT}</p>
{ADD_NEW} <input type="submit" name="delete" id="phpws_form_delete" value="Delete slot {SORT}">
{END_FORM}