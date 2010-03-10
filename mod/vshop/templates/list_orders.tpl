<table width="99%" cellpadding="4">
    <tr>
        <th>{ORDER_HEADER} {ID_SORT}</th>
        <th>{TOTAL_HEADER} {TOTAL_SORT}</th>
        <th>{ORDERED_HEADER} {ORDER_DATE_SORT}</th>
        <th>{UPDATED_HEADER} {UPDATE_DATE_SORT}</th>
        <th>{STATUS_HEADER} {STATUS_SORT}</th>
        <th>&nbsp;</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr{TOGGLE}>
        <td>{ORDER}</td>
        <td>{TOTAL}</td>
        <td>{ORDERED}</td>
        <td>{UPDATED}</td>
        <td>{STATUS}</td>
        <td>{ACTION}</td>
    </tr>
    <tr{TOGGLE}>
        <td colspan="6" class="smaller">{DESCRIPTION}</td>
    </tr>
    <!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<div class="align-center">{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}</div>
<div class="align-right">{SEARCH}</div>
