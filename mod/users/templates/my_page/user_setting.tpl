{START_FORM}
<table cellspacing="0" cellpadding="4" width="100%">
  <!-- BEGIN password-change -->
  <tr>
    <td><b>{PASSWORD1_LABEL}</b></td><td>{PASSWORD1}&nbsp;{PASSWORD2}</td>
  </tr>
  <!-- BEGIN password-error -->
  <tr><td class="user-error" colspan="2">{PASSWORD_ERROR}</td></tr>
  <!-- END password-error -->
  <!-- END password-change -->
  <tr class="bg-light">
    <td><b>{EMAIL_LABEL}</b></td><td>{EMAIL}</td>
  </tr>
  <!-- BEGIN email-error -->
  <tr><td class="user-error" colspan="2">{EMAIL_ERROR}</td></tr>
  <!-- END email-error -->
</table>
{SUBMIT}
{END_FORM}
