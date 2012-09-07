{PAGE_FORWARDING}<br />
{PAGE_FIX}<br />
{MENU_FIX} (<i>{MENU_WARNING}</i>)
{START_FORM}
<table width="99%" cellpadding="3">
    <tr>
        <th width="%3">&nbsp;</th>
        <th>{URL_LABEL} {KEYWORD_SORT}</th>
        <th>{ACTIVE_LABEL}</th>
        <th>{ACTION_LABEL}</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr{TOGGLE}>
        <td>{CHECKBOX}</td>
        <td>{URL}</td>
        <td>{ACTIVE}</td>
        <td>{ACTION}</td>
    </tr>
    <!-- END listrows -->
</table>
<!-- BEGIN empty -->
<div class="align-center">{EMPTY_MESSAGE}</div>
<div class="align-center">{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}</div>
<!-- END empty -->
<div style="float : right">{SEARCH}</div>
<div>{CHECK_ALL_SHORTCUTS}<br /> {LIST_ACTION} {SUBMIT}</div>
{END_FORM}
