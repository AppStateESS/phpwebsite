
{START_FORM}
<h3>{TITLE}&#160;{NAV_INFO}</h3>
<table border="0" cellspacing="1" cellpadding="4">
<tr class="bg-dark">
<td width="10%" align="center"><b>{ACTIVE_LABEL}</b></td>
<td align="left"><b>{PROPER_NAME_LABEL}&#160;{PROPER_NAME_ORDER_LINK}</b></td>
<td align="left"><b>{INPUT_TYPE_LABEL}&#160;{INPUT_TYPE_ORDER_LINK}</b></td>
<td align="right"><b>{ACTIONS_LABEL}</b></td>
</tr>
<!-- BEGIN row -->
<tr{ROW_CLASS}>
<td align="center">
{ACTIVE}
</td>
<td align="left">
{PROPER_NAME}
</td>
<td align="left">
{INPUT_TYPE}
</td>
<td align="right">
{ACTIONS}
</td>
</tr>
<!-- END row -->
</table>
{DEFAULT_SUBMIT}
{END_FORM}
<br />
<!-- BEGIN navigation -->
<div align="center">{NAV_BACKWARD}&#160;{NAV_SECTIONS}&#160;{NAV_FORWARD}<br />{NAV_LIMITS}</div>
<!-- END navigation -->
