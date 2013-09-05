<div id="{REDUCE}"><!-- BEGIN options -->
<p>{SMALL_VIEW} | {REFRESH}</p>
<!-- END options -->
<div id="action-buttons">{SENDBACK} {MEET} {AVAILABLE}
{UNAVAILABLE} {FINISH} {SEND_STUDENT}</div>
<div id="current-meeting" class="{CURRENT_CLASS}">{CURRENT_MEETING}</div>
<table width="100%" cellpadding="6" style="clear: both">
    <tr>
        <th>{NAME_LABEL}</th>
        <th>{WAITING_LABEL}</th>
        <th>&#160;</th>
    </tr>
    <!-- BEGIN list -->
    <tr>
        <td>{NAME}<!-- BEGIN notes --><br />
        <em style="font-size: .9em">{NOTE}</em><!-- END notes --></td>
        <td>{WAITING}</td>
        <td>{ACTION}<br />{MOVE}</td>
    </tr>
    <!-- END list -->
</table>
{MESSAGE}</div>
<p style="text-align: center">{CLOSE}</p>