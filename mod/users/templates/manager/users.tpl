<table border="0" cellspacing="1" cellpadding="4" width="100%" id="user-manager">
<tr>
<th>{USERNAME_SORT}<br />
{DISPLAY_NAME_SORT}</th>
<th>{EMAIL_SORT}</th>
<th>{LAST_LOGGED_SORT}</th>
<th width="5%">{ACTIVE_LABEL}</th>
<th>{ACTIONS_LABEL}</th>
</tr>
<!-- BEGIN listrows -->
<tr {TOGGLE}>
<td>
{USERNAME}<br />{DISPLAY_NAME}
</td>
<td>
{EMAIL}
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
{EMPTY_MESSAGE}
<br />
<div class="align-center">
{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}{PAGE_DROP}<br />
{LIMIT_LABEL} {LIMITS}
</div>
<div class="align-right">
{SEARCH}
</div>
