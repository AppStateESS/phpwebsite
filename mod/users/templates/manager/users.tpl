<table border="0" cellspacing="1" cellpadding="4" width="100%" id="user-manager">
<tr class="bg-dark">
<td width="20%"><span style="font-weight : bold">{USERNAME}&#160;{USERNAME_SORT}</span></td>
<td width="20%"><span style="font-weight : bold">{LAST_LOGGED}&#160;{LAST_LOGGED_SORT}</span></td>
<td width="10%"><span style="font-weight : bold">{ACTIVE}</span></td>
<td><span style="font-weight : bold">{ACTIONS}</span></td>
</tr>
<!-- BEGIN listrows -->
<tr {TOGGLE}>
<td>
{USERNAME}
</td>
<td class="last-logged">
{LAST_LOGGED}
</td>
<td align="left">
{ACTIVE}
</td>
<td>{ACTIONS}</td>
</tr>
<!-- END listrows -->
</table>

{PAGES}<br />
{LIMITS}
