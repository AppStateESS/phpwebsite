<!-- BEGIN message -->
<span class="smalltext">{MESSAGE}</span>
<hr />
<!-- END message -->
<div class="align-right">{LINKS}</div>
<div>
    {START_FORM}
    {NOTIFY_USER} {NOTIFY_USER_LABEL}
    <table class="table table-striped" cellspacing="0" cellpadding="4">
        <tr>
            <td>{AUTHORIZE_LABEL}</td>
            <td>{AUTHORIZE}</td>
        </tr>
        <!-- BEGIN authorize-error -->
        <tr>
            <td class="error" colspan="2">{AUTHORIZE_ERROR}</td>
        </tr>
        <!-- END authorize-error -->
        <!-- BEGIN username -->
        <tr>
            <td>{USERNAME_LABEL}</td>
            <td>{USERNAME}</td>
        </tr>
        <!-- END username -->
        <!-- BEGIN username-error -->
        <tr>
            <td class="user-error" colspan="2">{USERNAME_ERROR}</td>
        </tr>
        <!-- END username-error -->
        <tr>
            <td>{DISPLAY_NAME_LABEL}</td>
            <td>{DISPLAY_NAME}</td>
        </tr>
        <!-- BEGIN password -->
        <tr>
            <td>{PASSWORD1_LABEL}</td>
            <td>{PASSWORD1}&nbsp;{PASSWORD2}</td>
        </tr>
        <!-- END password -->
        <!-- BEGIN password-error -->
        <tr>
            <td class="user-error" colspan="2">{PASSWORD_ERROR}</td>
        </tr>
        <!-- END password-error -->
        <!-- BEGIN generate -->
        <tr>
            <td>&#160;</td>
            <td>{CREATE_PW} <span id="generated-password"></span></td>
        </tr>
        <!-- END generate -->
        <tr>
            <td>{EMAIL_LABEL}</td>
            <td>{EMAIL}</td>
        </tr>
        <!-- BEGIN email-error -->
        <tr>
            <td class="user-error" colspan="2">{EMAIL_ERROR}</td>
        </tr>
        <!-- END email-error -->
    </table>
    <div class="text-center">{GO}</div>
    {END_FORM}
</div>
<fieldset>
    <legend>Group member</legend>
    {EMPTY_GROUP}
    <ul>
        <!-- BEGIN members -->
        <li>{NAME}</li>
        <!-- END members -->
    </ul>
</fieldset>