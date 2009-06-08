{START_FORM}
<fieldset>
    <legend><strong>{PROFILE_GROUP_LABEL}</strong></legend>
    <div style="float: left; text-align: left;">
        {COURTESY_TITLE}<br />{COURTESY_TITLE_LABEL}
    </div>
    <div style="float: left;">
        {FIRST_NAME}<br />{FIRST_NAME_LABEL}
    </div>
    <div style="float: left; text-align: center;">
        {MIDDLE_INITIAL}<br />{MIDDLE_INITIAL_LABEL}
    </div>
    <div style="float: left;">
        {LAST_NAME}<br />{LAST_NAME_LABEL}
    </div>
    <div style="float: left;">
        {HONORIFIC}<br />{HONORIFIC_LABEL}
    </div>
    <br style="clear: both" />
    <!-- BEGIN edit_user --><p>{EDIT_USER}<br />{ACTIVE_LINK}</p><!-- END edit_user -->
    <p>{BUSINESS_NAME} {BUSINESS_NAME_LABEL}</p>
    <p>{DEPARTMENT} {DEPARTMENT_LABEL}<br />{POSITION_TITLE} {POSITION_TITLE_LABEL}</p>
    <p>{DESCRIPTION_LABEL}<br />{DESCRIPTION}</p>
    <p>{IMAGE} {IMAGE_LABEL}</p>
    <!-- BEGIN current_image --><p>{CURRENT_IMAGE} {CURRENT_THUMB} {CLEAR_IMAGE} {CLEAR_IMAGE_LABEL}</p><!-- END current_image -->
</fieldset>
<fieldset>
    <legend><strong>{CONTACT_GROUP_LABEL}</strong></legend>
    <fieldset>
        <p>{CONTACT_EMAIL} {CONTACT_EMAIL_LABEL}</p>
        <p>{WEBSITE} {WEBSITE_LABEL}</p>
        <p>{DAY_PHONE} {DAY_PHONE_LABEL} {DAY_PHONE_EXT} {DAY_PHONE_EXT_LABEL} <br />
           {TOLLFREE_PHONE} {TOLLFREE_PHONE_LABEL} <br />
           {FAX_NUMBER} {FAX_NUMBER_LABEL}<br />
           {EVENING_PHONE} {EVENING_PHONE_LABEL}
         </p>
    </fieldset>
    <fieldset>
        <legend><strong>{HOME_LABEL}</strong></legend>
        <p>{MAILING_ADDRESS_1}<br />{MAILING_ADDRESS_1_LABEL}</p>
        <p>{MAILING_ADDRESS_2}<br />{MAILING_ADDRESS_2_LABEL}</p>
        <p>{MAILING_CITY} {MAILING_CITY_LABEL}<br />{MAILING_STATE} {MAILING_STATE_LABEL} </p>
        <p>{MAILING_COUNTRY} {MAILING_COUNTRY_LABEL}<br />{MAILING_ZIP_CODE} {MAILING_ZIP_CODE_LABEL}</p>
    </fieldset>
    <fieldset>
        <legend><strong>{BUSINESS_LABEL}</strong></legend>
        <p>{BUSINESS_ADDRESS_1}<br />{BUSINESS_ADDRESS_1_LABEL}</p>
        <p>{BUSINESS_ADDRESS_2}<br />{BUSINESS_ADDRESS_2_LABEL}</p>
        <p>{BUSINESS_CITY} {BUSINESS_CITY_LABEL}<br />{BUSINESS_STATE} {BUSINESS_STATE_LABEL} </p>
        <p>{BUSINESS_COUNTRY} {BUSINESS_COUNTRY_LABEL}<br />{BUSINESS_ZIP_CODE} {BUSINESS_ZIP_CODE_LABEL}</p>
    </fieldset>
</fieldset>
<fieldset>
    <legend><strong>{SETTINGS_GROUP_LABEL}</strong></legend>
    <!-- BEGIN select_list_tip --><p>{SELECT_LIST_TIP}</p><!-- END select_list_tip -->
    <!-- BEGIN categories_label --><p>{CATEGORIES_LABEL}<br />{CATEGORIES}</p><!-- END categories_label -->
    <!-- BEGIN locations_label --><p>{LOCATIONS_LABEL}<br />{LOCATIONS}</p><!-- END locations_label -->
    <!-- BEGIN features_label --><p>{FEATURES_LABEL}<br />{FEATURES}</p><!-- END features_label -->
    <p>{ALLOW_COMMENTS} {ALLOW_COMMENTS_LABEL}</p>
    <p>{ALLOW_ANON} {ALLOW_ANON_LABEL}</p>
    <p>{DATE_EXPIRES} {DATE_EXPIRES_LABEL} {EXPIRES_CAL}</p>
    <p>{ACTIVE} {ACTIVE_LABEL}</p>
    <p>{PRIVACY} {PRIVACY_LABEL}</p>
    <p>{EMAIL_PRIVACY} {EMAIL_PRIVACY_LABEL}</p>
</fieldset>
<!-- BEGIN meta_group_label -->
<fieldset>
    <legend><strong>{META_GROUP_LABEL}</strong></legend>
    <div class="smaller">{FIELDS_NOTE}</div>
    <p>{CUSTOM1} {CUSTOM1_LABEL}</p>
    <p>{CUSTOM2} {CUSTOM2_LABEL}</p>
    <p>{CUSTOM3} {CUSTOM3_LABEL}</p>
    <p>{CUSTOM4} {CUSTOM4_LABEL}</p>
    <p>{CUSTOM5} {CUSTOM5_LABEL}</p>
    <p>{CUSTOM6} {CUSTOM6_LABEL}</p>
    <p>{CUSTOM7} {CUSTOM7_LABEL}</p>
    <p>{CUSTOM8} {CUSTOM8_LABEL}</p>
</fieldset>
<!-- END meta_group_label -->
{SAVE}
{END_FORM}
