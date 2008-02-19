<div class="comments">
<table class="comment-table">
    <tr>
        <td colspan="2" class="bgcolor3 smaller padded" >{RELATIVE_CREATE}
            <!-- BEGIN response --> - {RESPONSE_LABEL} {RESPONSE_NAME}<!-- END response -->
    <tr>
        <td class="author-info bgcolor1" valign="top">
           <h2>{AUTHOR_NAME} {ANONYMOUS_TAG}<!-- BEGIN ip -->- {IP_ADDRESS}<!-- END ip --></h2>
           {AVATAR}
        </td>
        <td class="comment-body">
            <h2>{VIEW_LINK}</h2>
            <div class="entry" style="border-top : 1px gray dotted">{ENTRY}</div>
            <!-- BEGIN signature --><div class="signature">{SIGNATURE}</div><!-- END signature -->
            <!-- BEGIN edit-info --><p class="edit-info">{EDIT_LABEL}: {EDIT_AUTHOR} ({EDIT_TIME})
            <!-- BEGIN reason --><br />{EDIT_REASON_LABEL}: {EDIT_REASON}<!-- END reason --></p>
            <!-- END edit-info -->
            <div class="admin-links"><!-- BEGIN edit-link -->{EDIT_LINK}
                <!-- BEGIN delete-link -->| {DELETE_LINK}<!-- END delete-link -->
                | <!-- END edit-link -->
                <!-- BEGIN post -->{REPLY_LINK} | {QUOTE_LINK} | {REPORT_LINK}<!-- END post -->
            </div>
        </td>
    </tr>
</table>
{CHILDREN}
</div>
