<div class="box">
    <div class="box-title">
        <h2 class="subject">{SUBJECT_LABEL}: {SUBJECT}</h2>
        <p class="posted-info">{POSTED_BY}: {AUTHOR_NAME} {ANONYMOUS_TAG} - {CREATE_TIME} ({VIEW_LINK})
            <!-- BEGIN ip-address -->({IP_ADDRESS})<!-- END ip-address -->
            <!-- BEGIN response --><br /> {RESPONSE_LABEL} {RESPONSE_NAME}<!-- END response -->
            <!-- BEGIN edit-time --><br />{EDIT_TIME_LABEL}: {EDIT_TIME}<!-- END edit-time -->
        </p>
    </div>
    <div class="box-content">
        <div class="entry">{ENTRY}</div>
        <!-- BEGIN signature --><div class="signature">{SIGNATURE}</div><!-- END signature -->
        <!-- BEGIN edit-info --><p class="edit-info">{EDIT_LABEL}: {EDIT_AUTHOR} ({EDIT_TIME})
        <!-- BEGIN reason --> - {EDIT_REASON_LABEL}: {EDIT_REASON}<!-- END reason --></p>
        <!-- END edit-info -->
        <div class="admin-links"><!-- BEGIN edit-link -->{EDIT_LINK}
            <!-- BEGIN delete-link -->| {DELETE_LINK}<!-- END delete-link -->
            <!-- BEGIN punish-link -->| {PUNISH_LINK}<!-- END punish-link -->
            | <!-- END edit-link -->
            <!-- BEGIN post -->{REPLY_LINK} | {QUOTE_LINK} | {REPORT_LINK}<!-- END post -->
        </div>
    </div>
</div>
{CHILDREN}
