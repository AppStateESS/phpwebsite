{TITLE}&#160;{NAV_INFO}
<table border="0" cellspacing="1" cellpadding="4" width="100%" id="user-manager">
<tr class="bg-dark">
<td width="20%"><span style="font-weight : bold">{NAME_ORDER_LINK}&#160;{NAME_LABEL}</span></td>
<td width="15%"><span style="font-weight : bold">{MEMBERS_ORDER_LINK}&#160;{MEMBERS_LABEL}</span></td>
<td width="10%"><span style="font-weight : bold">{ACTIVE_ORDER_LINK}&#160;{ACTIVE_LABEL}</span></td>
<td><span style="font-weight : bold">{ACTIONS_LABEL}</span></td>
</tr>
<!-- BEGIN row -->
<tr{ROW_CLASS}>
<td>
{NAME}
</td>
<td align="center">
{MEMBERS}
</td>
<td align="left">
{ACTIVE}
</td>
<td>{ACTIONS}</td>
</tr>
<!-- END row -->
</table>
{DEFAULT_SUBMIT}
<br />
<!-- BEGIN navigation -->
<div align="center">{NAV_BACKWARD}&#160;{NAV_SECTIONS}&#160;{NAV_FORWARD}<br />{NAV_LIMITS}</div>
<!-- END navigation -->
{SEARCH_LABEL}{SEARCH}
