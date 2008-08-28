<div class="comments">
		<!-- open content container -->
    {ANCHOR}
    <table id="cm_{COMMENT_ID}" class="comment-table">
        <tr>
            <td colspan="2" class="comment-header bgcolor3 smaller" >
            	<div style="float: right">
            		{REPORT_LINK} {DELETE_LINK} {FORK_THIS} {PUNISH_LINK} {SELECT_THIS}
            	</div>
            	<div style="padding-top: .25em;">
            	{RELATIVE_CREATE}
                <!-- BEGIN response --> - {RESPONSE_LABEL} {RESPONSE_NAME}<!-- END response -->
            	</div>
            </td>
        <tr>
            <td class="author-info bgcolor1" valign="top">
               <h2>{AUTHOR_NAME} {ANONYMOUS_TAG}</h2>
				<div class="smaller">
				<!-- BEGIN RANK -->
				{RANK_LIST}<br />
				<!-- END RANK -->
				<!-- BEGIN AVATAR -->
				{AVATAR}<br />
				<!-- END AVATAR -->
				<!-- BEGIN MSINCE -->
				{JOINED_DATE_LABEL}: {JOINED_DATE}<br />
				<!-- END MSINCE -->
				<!-- BEGIN POSTCOUNT -->
				{COMMENTS_MADE_LABEL}: {COMMENTS_MADE}<br />
				<!-- END POSTCOUNT -->
				<!-- BEGIN LOC -->
				{LOCATION_LABEL}: {LOCATION}<br />
				<!-- END LOC -->
				<!-- BEGIN SITE -->
				{WEBSITE}<br />
				<!-- END SITE -->
				<br />
				<!-- BEGIN ip -->
				{IP_ADDRESS_LABEL}: {IP_ADDRESS}<br />
				<!-- END ip -->
				<!-- BEGIN NOTE -->
				{NOTE}<br />
				<!-- END NOTE -->
				<!-- BEGIN EDIT_SETTINGS -->
				{EDIT_USER}<br />
				<!-- END EDIT_SETTINGS -->
				</div>
            </td>
            <td class="comment-body">
                <h2>{VIEW_LINK}</h2>
                <div class="entry">{ENTRY}</div>
                <!-- BEGIN signature --><div class="signature">{SIGNATURE}</div><!-- END signature -->
                <!-- BEGIN edit-info --><p class="edit-info">{EDIT_LABEL}: {EDIT_AUTHOR} ({EDIT_TIME})
                <!-- BEGIN reason --><br />{EDIT_REASON_LABEL}: {EDIT_REASON}<!-- END reason --></p>
                <!-- END edit-info -->
                <div class="admin-links">
                    <!-- BEGIN post -->{EDIT_LINK} {REPLY_LINK} {QUOTE_LINK}<!-- END post -->
                </div>
            </td>
         </tr>
     </table>

	
	<!-- / close content container -->
<br />
<br />
<h2>{RESPONSES}</h2>
<br />
{CHILDREN}
</div>
