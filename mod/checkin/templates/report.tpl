<!-- BEGIN form -->
{START_FORM}
<strong>{CDATE_LABEL}</strong>
{CDATE} {SUBMIT} {END_FORM}
<!-- BEGIN links -->
<p>{REPEAT_VISITS} | {PRINT_LINK}</p>
<!-- END links -->
<!-- END form -->
<table border="1" width="99%" cellpadding="4">
    <!-- BEGIN row -->
    <tr>
        <td><strong>{DISPLAY_NAME}</strong><br />
        <span class="smaller"> {VISITORS_SEEN}<br />
        {TOTAL_WAIT}<br />
        {AVERAGE_WAIT}<br />
        {TOTAL_SPENT}</span></td>
        <td width="75%">
        <table cellpadding="5" width="100%">
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
            <tr>
                <td width="60%"><strong>{VIS_NAME}</strong><br />
                {REASON}<br />
                <em>{NOTE}</em></td>
                <td class="smaller">{ARRIVAL}</td>
                <td class="smaller">{WAITED}</td>
                <td class="smaller">{SPENT}</td>
            </tr>
            <!-- END subrow -->
        </table>
        </td>
    </tr>
    <!-- END row -->
</table>
