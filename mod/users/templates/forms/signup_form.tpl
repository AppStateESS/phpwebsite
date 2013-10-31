{START_FORM}
<table class="form-table">
    <tr>
        <td>{USERNAME_LABEL}</td>
        <td>{USERNAME} <!-- BEGIN username-error -->
        <div class="error">{USERNAME_ERROR}</div>
        <!-- END username-error --></td>
    </tr>
    <tr>
        <td>{PASSWORD1_LABEL}</td>
        <td>{PASSWORD1} {PASSWORD2_LABEL} {PASSWORD2} <!-- BEGIN password-error -->
        <div class="error">{PASSWORD_ERROR}</div>
        <!-- END password-error --></td>
    </tr>
    <tr>
        <td>{EMAIL_LABEL}</td>
        <td>{EMAIL} <!-- BEGIN email-error -->
        <div class="error">{EMAIL_ERROR}</div>
        <!-- END email-error --></td>
    </tr>
    <tr>
        <td>{CONFIRM_GRAPHIC_LABEL}</td>
        <td>{GRAPHIC}<!-- BEGIN graphic-error -->
        <div class="error">{CONFIRM_ERROR}</div>
        <!-- END graphic-error --></td>
    </tr>
</table>
{SUBMIT} {END_FORM}
