<table border="0" cellspacing="1" cellpadding="4" width="100%" id="user-manager">
<tr>
<th>{USERNAME}&#160;{USERNAME_SORT}</td>
<th>{LAST_LOGGED}&#160;{LAST_LOGGED_SORT}</td>
<th width="10%">{ACTIVE}</td>
<th>{ACTIONS}</td>
</tr>
<!-- BEGIN listrows -->
<tr {TOGGLE}>
<td>
{USERNAME}
</td>
<td style="font-size : .8em;">
{LAST_LOGGED}
</td>
<td align="left">
{ACTIVE}
</td>
<td>{ACTIONS}</td>
</tr>
<!-- END listrows -->
</table>
<br />
<div style="text-align : center">
{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}{PAGE_DROP}<br />
{LIMIT_LABEL} {LIMITS}
</div>
<div style="text-align : right">
{SEARCH}
</div>
