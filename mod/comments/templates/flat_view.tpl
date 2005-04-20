<div class="comments">
<div class="bgcolor1 padded">{NEW_POST_LINK}</div>
<div>{MESSAGE}</div>
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
  <!-- BEGIN edit-reason --><div class="smaller error padded">
  {EDIT_AUTHOR_LABEL}: {EDIT_AUTHOR}<br />
  <!-- BEGIN reason -->{EDIT_REASON_LABEL}: {EDIT_REASON}<!-- END reason -->
  </div><!-- END edit-reason -->
  <div class="align-right padded"><!-- BEGIN edit-link -->{EDIT_LINK}
  | <!-- END edit-link --> {REPLY_LINK}</div>
</div>
<!-- END listrows -->
</div>
