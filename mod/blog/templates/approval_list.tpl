<div style="margin-top : 1em">
<!-- BEGIN show-new-approval -->
  <h2>{NEW_LABEL}</h2>
  <!-- BEGIN new-approval -->
  <div style="margin : 1em">
    <div class="bgcolor1" style="padding : 3px">
      <div style="float: left;">
        <b>{DATE_LABEL}:</b> {DATE}<br />
	<b>{CREATOR_LABEL}:</b> {CREATOR}&nbsp;&nbsp;&nbsp;&nbsp;
	<!-- BEGIN editor-tag1 -->  <b>{EDITOR_LABEL}:</b> {EDITOR}<!-- END editor-tag1 -->
      </div>
      <div style="text-align: right;">{BLOG_LINKS}</div>
      <div style="clear : both"></div>
    </div>
    {ENTRY}
  </div>
<!-- END new-approval -->
<!-- END show-new-approval -->
<!-- BEGIN show-edit-approval -->
<hr />
<h2>{UPDATED_LABEL}</h2>
<!-- BEGIN update-approval -->
<div style="margin : 1em">
<div class="bgcolor1" style="padding : 3px">
  <div style="float: left;">
  <b>{DATE_LABEL}:</b> {DATE}<br />
  <b>{CREATOR_LABEL}:</b> {CREATOR}&nbsp;&nbsp;&nbsp;&nbsp;
  <!-- BEGIN editor-tag2 -->  <b>{EDITOR_LABEL}:</b> {EDITOR}<!-- END editor-tag2 -->
  </div>
  <div style="text-align: right;">{BLOG_LINKS}</div>
  <div style="clear : both"></div>
</div>
{ENTRY}
</div>
<!-- END update-approval -->
<!-- END show-edit-approval -->
</div>

