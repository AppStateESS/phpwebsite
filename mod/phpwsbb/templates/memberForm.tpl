{START_FORM}
<div class="align-right">{LINKS}</div>
<strong>{SEARCH_MEMBER_LABEL}:</strong><br />
{SEARCH_MEMBER}{ADD_MEMBER}{SEARCH}
<table border="0" width="100%">
  <tr>
    <td width="50%" valign="top">
		<h3>{MODERATORS_LBL}</h3>
<!-- BEGIN moderator_list -->
		<div {STYLE} style="padding : 2px">{ACTION} {NAME}</div>
<!-- END moderator_list -->
    </td>
    <td width="50%" valign="top">
	    <h3>{MESSAGE}</h3>
<!-- BEGIN suggestion_list -->
		<div {STYLE} style="padding : 2px">{ACTION} {NAME}</div>
<!-- END suggestion_list -->
    </td>
  </tr>
</table>
{END_FORM}
