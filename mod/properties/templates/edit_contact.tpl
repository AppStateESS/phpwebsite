{START_FORM}
{CONTACT_CONTACT} {CONTACT_CONTACT_LABEL}

<!-- BEGIN username -->
<div class="form-group">
    {USERNAME_LABEL}
    <!-- BEGIN error1 --><span class="label label-danger">{USERNAME_ERROR}</span><!-- END error1 -->
    {USERNAME}
</div>
<!-- END username -->

<div class="form-group">
    {PASSWORD_LABEL}
     <!-- BEGIN error2 --><span class="label label-danger">{PASSWORD_ERROR}</span><!-- END error2 -->
    {PASSWORD}<br />
    {PW_CHECK_LABEL}<br />
    {PW_CHECK}
    {MAKE_PASSWORD}
</div>
<div id="password-created"></div>

<div class="form-group">
    {COMPANY_NAME_LABEL} (first and last name if single landlord)
    <!-- BEGIN error3 --><span class="label label-danger">{COMPANY_NAME_ERROR}</span><!-- END error3 -->
    {COMPANY_NAME}
</div>

<div class="form-group">
    {COMPANY_URL_LABEL} (Format: http://address.com)
    <!-- BEGIN error4 --><span class="label label-danger">{COMPANY_URL_ERROR}</span><!-- END error4 -->
    {COMPANY_URL}
</div>

<div class="form-group">
    {FIRST_NAME_LABEL}
    <!-- BEGIN error5 --><span class="label label-danger">{FIRST_NAME_ERROR}</span><!-- END error5 -->
    {FIRST_NAME}
</div>

<div class="form-group">
    {LAST_NAME_LABEL}
    <!-- BEGIN error6 --><span class="label label-danger">{LAST_NAME_ERROR}</span><!-- END error6 -->
    {LAST_NAME}
</div>

<div class="form-group">
    {PHONE_LABEL}
    <!-- BEGIN error7 --><span class="label label-danger">{PHONE_ERROR}</span><!-- END error7 -->
    {PHONE}
</div>

<div class="form-group">
    {EMAIL_ADDRESS_LABEL}
    <!-- BEGIN error8 --><span class="label label-danger">{EMAIL_ADDRESS_ERROR}</span><!-- END error8 -->
    {EMAIL_ADDRESS}
</div>

<div class="form-group">
    {COMPANY_ADDRESS_LABEL}
    <!-- BEGIN error9 --><span class="label label-danger">{COMPANY_ADDRESS_ERROR}</span><!-- END error9 -->
    {COMPANY_ADDRESS}
</div>

<div class="form-group">
    {TIMES_AVAILABLE_LABEL} (leaving this blank indicates you may be contacted at any time)
    <!-- BEGIN error10 --><span class="label label-danger">{TIMES_AVAILABLE_ERROR}</span><!-- END error10 -->
    {TIMES_AVAILABLE}
</div>

{SUBMIT} {END_FORM}
