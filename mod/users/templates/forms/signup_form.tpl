<h1>{SIGNUP_LABEL}</h1>
{START_FORM}
<table class="form-table">
  <tr>
    <td>{USERNAME_LABEL}
    <!-- START username-error -->
    <div class="error">{USERNAME_ERROR}</div>
    <!-- END username-error -->
    </td>
    <td>{USERNAME}
    </td>
  </tr>
  <tr>
    <td>{PASSWORD1_LABEL}
    </td>
    <td>{PASSWORD1} {PASSWORD2_LABEL} {PASSWORD2}
    <!-- BEGIN password-error -->
    {PASSWORD_ERROR}
    <!-- END password-error -->
    </td>
  </tr>
  <tr>
    <td>{CONFIRM_GRAPHIC_LABEL}
    </td>
    <td>{GRAPHIC}<br />{CONFIRM_GRAPHIC} {CONFIRM_INSTRUCTIONS}
    </td>
  </tr>
</table>
{SUBMIT}
{END_FORM}
