{TITLE}&#160;{NAV_INFO}
<table border="0" cellspacing="1" cellpadding="4" width="100%" id="user-manager">
<tr class="bg-dark">
<td width="20%"><b>{USERNAME_LABEL}</b></td>
<td width="20%"><b>{LAST_LOGGED_LABEL}&#160;{LAST_LOGGED_ORDER_LINK}</b></td>
<td width="10%"><b>{ACTIVE_LABEL}&#160;{APPROVED_ORDER_LINK}</b></td>
<td><b>{DEITY_LABEL}&#160;{DEITY_ORDER_LINK}</b></td>
<td><b>{ACTIONS_LABEL}</b></td>
</tr>
<!-- BEGIN row -->
<tr{ROW_CLASS}>
<td>
{USERNAME}
</td>
<td class="last-logged">
{LAST_LOGGED}
</td>
<td align="left">
{ACTIVE}
</td>
<td align="right">
{DEITY}
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
