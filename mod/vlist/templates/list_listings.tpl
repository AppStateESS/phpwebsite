{ALPHA_CLICK}
<!-- BEGIN item_title --><!--<h2>{ITEM_TITLE}</h2>--><!-- END item_title -->
<!-- BEGIN item_image --><div style="float: right; margin: 0.5em 0 0.5em 0.5em;">{ITEM_IMAGE}</div><!-- END item_image -->
<!-- BEGIN item_description --><p>{ITEM_DESCRIPTION}</p><!-- END item_description -->
<!-- BEGIN item_link --><p>{ITEM_LINK}</p><!-- END item_link -->
<!-- BEGIN item_clear_float -->{ITEM_CLEAR_FLOAT}<!-- END item_clear_float -->
<table width="99%" cellpadding="4">
    <tr>
        <th>{TITLE_SORT}</th>
        <!-- BEGIN owner_id_sort --><th>{OWNER_ID_SORT}</th><!-- END owner_id_sort -->
        <!-- BEGIN created_sort --><th>{CREATED_SORT}</th><!-- END created_sort -->
        <!-- BEGIN updated_sort --><th>{UPDATED_SORT}</th><!-- END updated_sort -->
        <!-- BEGIN EXTRA_TITLES -->
        <!-- BEGIN ext_title --><th>{EXT_TITLE}</th><!-- END ext_title -->
        <!-- END EXTRA_TITLES -->
        <th>&nbsp;</th>
    </tr>
<!-- BEGIN listrows -->
    <tr {TOGGLE}>
        <td>{TITLE}</td>
        <!-- BEGIN owner --><td>{OWNER}</td><!-- END owner -->
        <!-- BEGIN created --><td>{CREATED}</td><!-- END created -->
        <!-- BEGIN updated --><td>{UPDATED}</td><!-- END updated -->
        <!-- BEGIN EXTRA_VALUES -->
        {EXTRA_VALUES}
        <!-- END EXTRA_VALUES -->
        <td>{ACTION}</td>
    </tr>
    <tr {TOGGLE}>
        <td colspan="{COLSPAN}" class="smaller">
            <!-- BEGIN thumb --><div style="float: left; margin: 0 .5em .2em 0;">{THUMB}</div><!-- END thumb -->
            {DESCRIPTION}
            <!-- BEGIN group_links --><div>{GROUP_LINKS_LABEL}: <b>{GROUP_LINKS}</b></div><!-- END group_links -->
            <!-- BEGIN file --><div>{FILE_LABEL}: {FILE}</div><!-- END file -->
        </td>
    </tr>
<!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<div class="align-center">
    {TOTAL_ROWS}<br />
    {PAGE_LABEL} {PAGES}<br />
    {LIMIT_LABEL} {LIMITS}
</div>
<div class="align-right">
    {SEARCH} {CLEAR_FILTERS}
</div>
