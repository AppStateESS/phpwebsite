<!-- BEGIN login-form -->
<span class="pull-right">
    <form class="navbar-form {FORM_CLASS}" id="{FORM_ID}"
          action="{FORM_ACTION}" autocomplete="{FORM_AUTOCOMPLETE}"
          method="{FORM_METHOD}"{FORM_ENCODE}>
        {HIDDEN_FIELDS} {PHPWS_USERNAME} {PHPWS_PASSWORD}
        <button type="submit" class="btn">{SUBMIT_VALUE}</button>
    </form>
</span>
<!-- END login-form -->
<ul>
    <!-- BEGIN new-account -->
    <li class="pull-right">{NEW_ACCOUNT}</li>
    <!-- END new-account -->

    <!-- BEGIN forgot -->
    <li class="pull-right">{FORGOT}</li>
    <!-- END forgot -->

    <!-- BEGIN logged-in -->
    <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown">{DISPLAY_NAME} <b class="caret"></b></a>
        <ul class="dropdown-menu">
            <li>{HOME}</li>
            <li>{PANEL}</li>
            <li>{LOGOUT}</li>
        </ul>
    </li>
    <!-- END logged-in -->
</ul>