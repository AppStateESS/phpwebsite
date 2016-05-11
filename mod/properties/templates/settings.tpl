{START_FORM}
<div style="margin-bottom : 2em"><strong>{LOGIN_LINK_LABEL}</strong><br />
    {LOGIN_LINK}
    <!-- BEGIN error1 --><div class="error">
        <div class="arrow-left"></div>
        <div class="error-message">{LOGIN_LINK_ERROR}</div>
    </div><!-- END error1 -->
</div>

<div class="row">
    <div class="col-sm-6">
        {EMAIL_LABEL}<br />{EMAIL}
        <!-- BEGIN error2 --><div class="alert alert-danger">{EMAIL_ERROR}</div><!-- END error2 -->
    </div>
    <div class="col-sm-6">
        {APPROVER_EMAIL_LABEL}<br />{APPROVER_EMAIL}
        <!-- BEGIN error3 --><div class="alert alert-danger">{APPROVER_EMAIL_ERROR}</div><!-- END error3 -->
    </div>
</div>

<hr />
<p>{NEW_USER_SIGNUP} {NEW_USER_SIGNUP_LABEL}</p>

<p>{ROOMMATE_ONLY} {ROOMMATE_ONLY_LABEL}</p>
{SUBMIT} {END_FORM}
