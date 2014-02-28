<!-- BEGIN people -->
<table class="table table-striped">
    <tr>
        <th>&nbsp;</th>
        <th>{NAME_LABEL}, {EMAIL_LABEL}, {PHONE_LABEL}</th>
        <th>{EXTRA1_LABEL}</th>
        <th>{MOVE_LABEL}</th>
    </tr>
    <!-- BEGIN peep-row -->
    <tr>
        <td class="admin-icons">{ACTION}</td>
        <td>{FIRST_NAME} {LAST_NAME}<br />
        {EMAIL}<br />
        {PHONE}</td>
        <td><!-- BEGIN extra1 -->
        <div style="padding: 3px">{EXTRA1}</div>
        <!-- END extra1 --> <!-- BEGIN extra2 -->
        <div style="padding: 3px" class="bgcolor1">{EXTRA2}</div>
        <!-- END extra2 --> <!-- BEGIN extra3 -->
        <div style="padding: 3px">{EXTRA3}</div>
        <!-- END extra3 --></td>
        <td>{MOVE}</td>
    </tr>
    <!-- END peep-row -->
</table>
<!-- END people -->
