<table border="0" width="100%" cellspacing="1" cellpadding="4">
    <tr>
        <td colspan="7"><b>{TITLE}</b></td>
    </tr>
    <tr class="bgcolor2">
        <!-- BEGIN SELECT -->
        <td width="5%">{SELECT_LABEL}</td>
        <!-- END SELECT -->
        <td width="10%" align="center"><b>{ID_LABEL}&#160;{ID_ORDER_LINK}</b></td>
        <td width="30%"><b>{LABEL_LABEL}&#160;{LABEL_ORDER_LINK}</b></td>
        <td align="center"><b>{EDITOR_LABEL}&#160;{EDITOR_ORDER_LINK}</b></td>
        <td align="center"><b>{UPDATED_LABEL}&#160;{UPDATED_ORDER_LINK}</b></td>
        <td align="center"><b>{HIDDEN_LABEL}&#160;{HIDDEN_ORDER_LINK}</b></td>
    </tr>
    {LIST_ITEMS}
</table>
<!-- BEGIN ACTION_STUFF -->
<br />
<table border="0" width="100%">
    <tr>
        <td width="33%">{NAV_INFO}</td>
        <td width="33%" align="center">{NAV_BACKWARD}&#160;{NAV_SECTIONS}&#160;{NAV_FORWARD}<br />
        {NAV_LIMITS}</td>
        <td width="33%" align="right">{ACTION_SELECT}
        {ACTION_BUTTON}</td>
    </tr>
</table>
<!-- END ACTION_STUFF -->
