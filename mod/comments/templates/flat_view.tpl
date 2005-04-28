<br />
<div class="comments">
<div class="bgcolor1 padded">{NEW_POST_LINK}</div>
<div>{EMPTY_MESSAGE}</div>
<!-- BEGIN listrows -->
<div class="box">
  <div class="bgcolor1 padded">
    <h3>{SUBJECT_LABEL}: {SUBJECT}</h3>
    <span class="b i smaller">{POSTED_BY}: {AUTHOR_NAME} - {CREATE_TIME}
    <!-- BEGIN ip-address -->
    ({IP_ADDRESS})
    <!-- END ip-address -->
    <!-- BEGIN edit-time --><br />{EDIT_TIME_LABEL}: {EDIT_TIME}<!-- END edit-time -->
    </span>
  </div>
  <p class="padded">{ENTRY}</p>
  <!-- BEGIN edit-info --><div class="smaller error padded">
  {EDIT_AUTHOR_LABEL}: {EDIT_AUTHOR}
  <!-- BEGIN reason --><br />{EDIT_REASON_LABEL}: {EDIT_REASON}<!-- END reason -->
  </div><!-- END edit-info -->
  <div class="align-right padded"><!-- BEGIN edit-link -->{EDIT_LINK}
  | <!-- END edit-link --> {REPLY_LINK}</div>
</div>
<!-- END listrows -->
</div>
<div class="align-center">
  <b>{PAGE_LABEL}</b><br />
  {PAGES}<br />
  {LIMITS}
</div>
