{ALPHA_CLICK}
<!-- BEGIN item_title -->
<!--<h2>{ITEM_TITLE}</h2>-->
<!-- END item_title -->
<!-- BEGIN item_image -->
<div style="float: right; margin: 0.5em 0 0.5em 0.5em;">{ITEM_IMAGE}</div>
<!-- END item_image -->
<!-- BEGIN item_description -->
<p>{ITEM_DESCRIPTION}</p>
<!-- END item_description -->
<!-- BEGIN item_clear_float -->
{ITEM_CLEAR_FLOAT}
<!-- END item_clear_float -->
<table width="99%" cellpadding="4">
    <tr>
        <th>&nbsp;</th>
        <th>{TITLE_HEADER} {TITLE_SORT}</th>
        <th>{DATE_UPDATED_HEADER} {DATE_UPDATED_SORT}</th>
        <!-- BEGIN contact_email_header -->
        <th><!--{CONTACT_EMAIL_HEADER}--></th>
        <!-- END contact_email_header -->
        <!-- BEGIN website_header -->
        <th><!--{WEBSITE_HEADER}--></th>
        <!-- END website_header -->
        <!-- BEGIN custom1_header -->
        <th>{CUSTOM1_HEADER} {CUSTOM1_SORT}</th>
        <!-- END custom1_header -->
        <!-- BEGIN custom2_header -->
        <th>{CUSTOM2_HEADER} {CUSTOM2_SORT}</th>
        <!-- END custom2_header -->
        <!-- BEGIN custom3_header -->
        <th>{CUSTOM3_HEADER} {CUSTOM3_SORT}</th>
        <!-- END custom3_header -->
        <!-- BEGIN custom4_header -->
        <th>{CUSTOM4_HEADER} {CUSTOM4_SORT}</th>
        <!-- END custom4_header -->
        <!-- BEGIN custom5_header -->
        <th>{CUSTOM5_HEADER} {CUSTOM5_SORT}</th>
        <!-- END custom5_header -->
        <!-- BEGIN custom6_header -->
        <th>{CUSTOM6_HEADER} {CUSTOM6_SORT}</th>
        <!-- END custom6_header -->
        <!-- BEGIN custom7_header -->
        <th>{CUSTOM7_HEADER} {CUSTOM7_SORT}</th>
        <!-- END custom7_header -->
        <!-- BEGIN custom8_header -->
        <th>{CUSTOM8_HEADER} {CUSTOM8_SORT}</th>
        <!-- END custom8_header -->
        <th>&nbsp;</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr{TOGGLE}>
        <td rowspan="2" align="right">{THUMBNAIL}</td>
        <td><strong>{TITLE}</strong></td>
        <td>{DATE_UPDATED}</td>
        <!-- BEGIN contact_email_link -->
        <td>{CONTACT_EMAIL_LINK}</td>
        <!-- END contact_email_link -->
        <!-- BEGIN website -->
        <td>{WEBSITE_LINK}</td>
        <!-- END website -->
        <!-- BEGIN custom1 -->
        <td>{CUSTOM1}</td>
        <!-- END custom1 -->
        <!-- BEGIN custom2 -->
        <td>{CUSTOM2}</td>
        <!-- END custom2 -->
        <!-- BEGIN custom3 -->
        <td>{CUSTOM3}</td>
        <!-- END custom3 -->
        <!-- BEGIN custom4 -->
        <td>{CUSTOM4}</td>
        <!-- END custom4 -->
        <!-- BEGIN custom5 -->
        <td>{CUSTOM5}</td>
        <!-- END custom5 -->
        <!-- BEGIN custom6 -->
        <td>{CUSTOM6}</td>
        <!-- END custom6 -->
        <!-- BEGIN custom7 -->
        <td>{CUSTOM7}</td>
        <!-- END custom7 -->
        <!-- BEGIN custom8 -->
        <td>{CUSTOM8}</td>
        <!-- END custom8 -->
        <td>{ACTION}</td>
    </tr>
    <tr{TOGGLE}>
        <td colspan="14" class="smaller"><!-- BEGIN description -->{DESCRIPTION}<!-- END description -->
        <p><!-- BEGIN b_label --><br />
        <strong>{B_LABEL}</strong><!-- END b_label --> <!-- BEGIN b_address_1 --><br />
        {B_ADDRESS_1}<!-- END b_address_1 --> <!-- BEGIN b_address_2 --><br />
        {B_ADDRESS_2}<!-- END b_address_2 --> <!-- BEGIN b_city --><br />
        {B_CITY},<!-- END b_city --> <!-- BEGIN b_state -->{B_STATE}<!-- END b_state -->
        <!-- BEGIN b_country --><br />
        {B_COUNTRY}<!-- END b_country --> <!-- BEGIN b_zip_code -->{B_ZIP_CODE}<!-- END b_zip_code -->
        <!-- BEGIN b_google_map --><br />
        {B_GOOGLE_MAP}<br />
        <!-- END b_google_map --> <!-- BEGIN h_label --><br />
        <strong>{H_LABEL}</strong><!-- END h_label --> <!-- BEGIN h_address_1 --><br />
        {H_ADDRESS_1}<!-- END h_address_1 --> <!-- BEGIN h_address_2 --><br />
        {H_ADDRESS_2}<!-- END h_address_2 --> <!-- BEGIN h_city --><br />
        {H_CITY},<!-- END h_city --> <!-- BEGIN h_state -->{H_STATE}<!-- END h_state -->
        <!-- BEGIN h_country --><br />
        {H_COUNTRY}<!-- END h_country --> <!-- BEGIN h_zip_code -->{H_ZIP_CODE}<!-- END h_zip_code -->
        <!-- BEGIN h_google_map --><br />
        {H_GOOGLE_MAP}<br />
        <!-- END h_google_map --> <!-- BEGIN list_day_phone --><br />
        {LIST_DAY_PHONE_LABEL}: {LIST_DAY_PHONE}<!-- END list_day_phone -->
        <!-- BEGIN list_day_phone_ext -->{LIST_DAY_PHONE_EXT_LABEL}:
        {LIST_DAY_PHONE_EXT}<!-- END list_day_phone_ext --> <!-- BEGIN list_fax_number --><br />
        {LIST_FAX_NUMBER_LABEL}: {LIST_FAX_NUMBER}<!-- END list_fax_number -->
        <!-- BEGIN list_tollfree_phone --><br />
        {LIST_TOLLFREE_PHONE_LABEL}: {LIST_TOLLFREE_PHONE}<!-- END list_tollfree_phone -->
        <!-- BEGIN list_evening_phone --><br />
        {LIST_EVENING_PHONE_LABEL}: {LIST_EVENING_PHONE}<!-- END list_evening_phone -->
        </p>
        <!-- BEGIN category_links -->
        <div>{CATEGORY_LINKS_LABEL}: {CATEGORY_LINKS}</div>
        <!-- END category_links --> <!-- BEGIN location_links -->
        <div>{LOCATION_LINKS_LABEL}: {LOCATION_LINKS}</div>
        <!-- END location_links --> <!-- BEGIN feature_links -->
        <div>{FEATURE_LINKS_LABEL}: {FEATURE_LINKS}</div>
        <!-- END   feature_links --></td>
    </tr>
    <!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<div class="align-center">{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}</div>
<div class="align-right">{SEARCH} {CLEAR_FILTERS}</div>
