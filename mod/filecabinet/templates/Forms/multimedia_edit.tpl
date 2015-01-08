{START_FORM}
<!-- BEGIN error-list -->
<ul>
    <!-- BEGIN errors -->
    <li class="error">{ERROR}</li>
    <!-- END errors -->
</ul>
<!-- END error-list -->
<table class="form-table">
    <strong>{CURRENT_MULTIMEDIA_LABEL}</strong>
    <div style="float: left; margin-right: 10px">{CURRENT_MULTIMEDIA_ICON}</div>
    {CURRENT_MULTIMEDIA_FILE}<br />
    {EDIT_THUMBNAIL}
</table>
<p>
    {FILE_NAME_LABEL}<br />
    {FILE_NAME}
</p>

<p>
    {TITLE_LABEL}<br />
    {TITLE}
</p>

<p>
    {DESCRIPTION_LABEL}<br />
    <textarea name="description" title="Description" class="form-control"></textarea>
</p>
<!-- BEGIN dimensions -->
<p>
    {WIDTH_LABEL}
    {WIDTH}
</p>
<p>
    {HEIGHT_LABEL}
    {HEIGHT}
</p>
<!-- END dimensions -->
<!-- BEGIN move -->
<p>
    {MOVE_TO_FOLDER_LABEL}
    {MOVE_TO_FOLDER}
</p>
<!-- END move -->
{END_FORM}
<br />
{MAX_SIZE_LABEL}: {MAX_SIZE}
<br />
