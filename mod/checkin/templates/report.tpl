<!-- BEGIN form -->{START_FORM}
<strong>{CDATE_LABEL}</strong> {CDATE} {SUBMIT}
{END_FORM}
<p>{PRINT_LINK}</p>
<!-- END form -->
<table border="1" width="99%" cellpadding="4">
  <!-- BEGIN row --><tr>
    <td>
       <strong>{DISPLAY_NAME}</strong><br />
       {VISITORS_SEEN}<br />
       {TOTAL_WAIT}<br />
       {AVERAGE_WAIT}
    </td>
    <td width="75%">
      <table cellpadding="5" width="100%">
        <tr>
          <th>{NAME_LABEL}</th>
          <th>{ARRIVAL_LABEL}</th>
          <th>{WAITED_LABEL}</th>
          <th>{SPENT_LABEL}</th>
        </tr>
        <!-- BEGIN message --><tr><td colspan="2">{NOBODY}</td></tr><!-- END message -->
        <!-- BEGIN subrow --><tr>
          <td width="60%"><strong>{VIS_NAME}</strong><br />{REASON}<br /><em>{NOTE}</em></td>
          <td><span style="font-size : 90%">{ARRIVAL}</span></td>
          <td><span style="font-size : 90%">{WAITED}</span></td>
          <td><span style="font-size : 90%">{SPENT}</span></td>
        </tr><!-- END subrow -->
      </table>
    </td>
  </tr><!-- END row -->
</table>
