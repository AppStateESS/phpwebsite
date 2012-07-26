{START_FORM}
<p>{USERNAME_LABEL}<br />
{USERNAME}</p>
<fieldset><legend>{FILTER_LEGEND}</legend>
<table>
    <tr><td>{LAST_NAME} {LAST_NAME_LABEL}</td><td>{LAST_NAME_FILTER}</br >{LAST_NAME_FILTER_LABEL}</td></tr>
    <!-- BEGIN reason --><tr><td>{REASON} {REASON_LABEL}</td><td>{REASON_FILTER}</td></tr><!-- END reason -->
    <!-- BEGIN gender --><tr><td>{GENDER} {GENDER_LABEL}</td><td>{GENDER_FILTER}</td></tr><!-- END gender -->
    <!-- BEGIN birthdate --><tr><td>{BIRTHDATE} {BIRTHDATE_LABEL}</td><td>{START_DATE} to {END_DATE}</td></tr><!-- END birthdate -->
</table>
</fieldset>
{SUBMIT} {END_FORM}
