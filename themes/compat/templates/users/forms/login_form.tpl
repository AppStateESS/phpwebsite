{START_FORM}
<table>
    <tr>
        <td class="label">{PHPWS_USERNAME_LABEL}</td>
        <td>{PHPWS_USERNAME}</td>
    </tr>
    <tr>
        <td class="label">{PHPWS_PASSWORD_LABEL}</td>
        <td>{PHPWS_PASSWORD}</td>
    </tr>
    <!-- BEGIN graphic-confirm -->
    <tr>
        <td>{CONFIRM_GRAPHIC_LABEL}</td>
        <td>{GRAPHIC}<br />
        {CONFIRM_GRAPHIC} {CONFIRM_INSTRUCTIONS} <!-- BEGIN graphic-error -->
        <div class="error">{CONFIRM_ERROR}</div>
        <!-- END graphic-error --></td>
    </tr>
    <!-- END graphic-confirm -->
</table>
{SUBMIT} {END_FORM}
