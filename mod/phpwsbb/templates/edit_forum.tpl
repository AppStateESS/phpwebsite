<div class="box">
<div class="box-content">
    <!-- BEGIN error -->
    <div class="error">
    	{ERROR}
    </div>
    <!-- END error -->
    {BACK_LINK}
    <br />
    <br />
    {START_FORM}
    {TITLE_LABEL}:
    <br />
    {TITLE}
    <br />
    <br />
    {DESCRIPTION_LABEL}:
    <br />
    {DESCRIPTION}
    <br />
    <br />
    {SORTORDER_LABEL}: {SORTORDER} {SORTORDER_HELP}
    <br />
    <br />
    {ALLOW_ANON} {ALLOW_ANON_LABEL}
    <br />
    {DEFAULT_APPROVAL_LABEL} {DEFAULT_APPROVAL}
    <br />
    <br />
    {LOCK} {LOCK_LABEL}
    <br />
    <br />
    {CATEGORY_LABEL}: {CATEGORY}
    <br />
    <br />
    {SUBMIT}
    <br />
    <br />
    
    {SEARCH_MEMBER_LABEL}:
    <br />
    {SEARCH_MEMBER}{ADD_MEMBER}{SEARCH}
    <br />
    <br />
    <table border="0" width="100%">
    	<tr>
    		<td width="50%" valign="top">
    			<h3>{CURRENT_MODERATORS_LBL}</h3>
    			<!-- BEGIN moderator_list -->
    			<div {STYLE}>{ACTION} {NAME}</div>
    			<!-- END moderator_list -->
    		</td>
    		<td width="50%" valign="top">
    			<!-- BEGIN MSG -->
    			<h3>{SUGGESTION_MESSAGE}</h3>
    			<!-- END MSG -->
    			<!-- BEGIN suggestion_list -->
    			<div {STYLE}>{ACTION} {NAME}</div>
    			<!-- END suggestion_list -->
    		</td>
    	</tr>
    </table>
    
    <br />
    <br />
    
    {SUBMIT}
    {END_FORM}
</div>
</div>