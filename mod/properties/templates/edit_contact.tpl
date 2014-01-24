{START_FORM}
{CONTACT_CONTACT} {CONTACT_CONTACT_LABEL}
<table cellpadding="6" cellspacing="0" style="width: 100%">
    <!-- BEGIN username --><tr>
        <td style="width : 35%">{USERNAME_LABEL}</td>
        <td>{USERNAME}<!--  BEGIN error1 -->
        <div class="error">
        <div class="arrow-left"></div>
        <div class="error-message">{USERNAME_ERROR}</div>
        </div>
        <!-- END error1 --></td>
    </tr><!-- END username -->
    <tr>
        <td>{PASSWORD_LABEL}</td>
        <td>{PASSWORD}<br />
        {PW_CHECK_LABEL}<br />
        {PW_CHECK}<!--  BEGIN error2 -->
        <div class="error">
        <div class="arrow-left"></div>
        <div class="error-message">{PASSWORD_ERROR}</div>
        </div>
        <!-- END error2 -->{MAKE_PASSWORD}<div id="password-created"></div></td>
    </tr>
    <tr>
        <td>{COMPANY_NAME_LABEL}<br /><span style="font-size: .9em">(first and last name if single landlord)</span></td>
        <td>{COMPANY_NAME}<!--  BEGIN error3 -->
        <div class="error">
        <div class="arrow-left"></div>
        <div class="error-message">{COMPANY_NAME_ERROR}</div>
        </div>
        <!-- END error3 --></td>
    </tr>
    <tr>
        <td>{COMPANY_URL_LABEL}<br /><span style="font-size: .9em">(Format: http://address.com)</span></td>
        <td>{COMPANY_URL}<!--  BEGIN error8 -->
        <div class="error">
        <div class="arrow-left"></div>
        <div class="error-message">{COMPANY_URL_ERROR}</div>
        </div>
        <!-- END error8 --></td>
    </tr>
    <tr>
        <td>{FIRST_NAME_LABEL}</td>
        <td>{FIRST_NAME}<!--  BEGIN error4 -->
        <div class="error">
        <div class="arrow-left"></div>
        <div class="error-message">{FIRST_NAME_ERROR}</div>
        </div>
        <!-- END error4 --></td>
    </tr>
    <tr>
        <td>{LAST_NAME_LABEL}</td>
        <td>{LAST_NAME}<!--  BEGIN error5 -->
        <div class="error">
        <div class="arrow-left"></div>
        <div class="error-message">{LAST_NAME_ERROR}</div>
        </div>
        <!-- END error5 --></td>
    </tr>
    <tr>
        <td>{PHONE_LABEL}</td>
        <td>{PHONE} (10-digit)<!--  BEGIN error6 -->
        <div class="error">
        <div class="arrow-left"></div>
        <div class="error-message">{PHONE_ERROR}</div>
        </div>
        <!-- END error6 --></td>
    </tr>
    <tr>
        <td>{EMAIL_ADDRESS_LABEL}</td>
        <td>{EMAIL_ADDRESS}<!--  BEGIN error7 -->
        <div class="error">
        <div class="arrow-left"></div>
        <div class="error-message">{EMAIL_ADDRESS_ERROR}</div>
        </div>
        <!-- END error7 --></td>
    </tr>
    <tr>
        <td>{COMPANY_ADDRESS_LABEL}</td>
        <td>{COMPANY_ADDRESS}</td>
    </tr>
    <tr>
        <td>{TIMES_AVAILABLE_LABEL}<br /><span style="font-size: .9em">(leaving this blank indicates you may be contacted at any time)</span></td>
        <td>{TIMES_AVAILABLE}</td>
    </tr>
</table>
{SUBMIT} {END_FORM}
