<p>{PRINT_LINK}</p>
<p>{VISITORS_SEEN}<br />
{TOTAL_WAIT}<br />
{AVERAGE_WAIT}<br />
{TOTAL_SPENT}</p>
<table cellpadding="5" width="100%" style="">
    <tr>
        <th>{NAME_LABEL}</th>
        <th>{ARRIVAL_LABEL}</th>
        <th>{WAITED_LABEL}</th>
        <th>{SPENT_LABEL}</th>
    </tr>
    <!-- BEGIN message -->
    <tr>
        <td colspan="2">{NOBODY}</td>
    </tr>
    <!-- END message -->
    <!-- BEGIN subrow -->
    <!-- BEGIN visitors -->
    <!-- BEGIN date -->
    <tr>
        <th colspan="4">{DATE}</th>
    </tr>
    <!-- END date -->
    <tr>
        <td width="60%"><strong>{VIS_NAME}</strong><br />
        {REASON}<br />
        <em>{NOTE}</em></td>
        <td>{ARRIVAL}</td>
        <td>{WAITED}</td>
        <td>{SPENT}</td>
    </tr>
    <!-- END visitors -->
</table>