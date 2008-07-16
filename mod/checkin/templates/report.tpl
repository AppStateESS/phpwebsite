<!-- BEGIN form -->{START_FORM}
<strong>{CDATE_LABEL}</strong> {CDATE} {CAL} {SUBMIT}
{END_FORM}
<p>{PRINT_LINK}</p>
<!-- END form -->
<table border="1" width="99%" cellpadding="4">
  <!-- BEGIN row --><tr>
    <td><strong>{DISPLAY_NAME}</strong><br />{VISITORS_SEEN}<br />{TOTAL_WAIT}</td>
    <td>
      <table cellpadding="5" width="100%">
        <tr>
          <th>{NAME_LABEL}</th>
          <th>{WAITED_LABEL}</th>
        </tr>
        <!-- BEGIN message --><tr><td colspan="2">{NOBODY}</td></tr><!-- END message -->
        <!-- BEGIN subrow --><tr>
          <td><strong>{VIS_NAME}</strong><br />{REASON}<br /><em>{NOTE}</em></td>
          <td>{WAITED}</td>
        </tr><!-- END subrow -->
      </table>
    </td>
  </tr><!-- END row -->
</table>