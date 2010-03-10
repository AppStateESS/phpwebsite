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
<!-- END empty -->
{CHECK_ALL_SHORTCUTS}
<div class="align-center">{LIST_ACTION} {SUBMIT}</div>
{END_FORM}
