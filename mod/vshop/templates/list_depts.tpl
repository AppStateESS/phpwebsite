<table width="99%" cellpadding="4">
    <tr>
        <th></th>
        <th>{TITLE_HEADER} {TITLE_SORT}</th>
        <th>{ITEMS_HEADER}</th>
        <th>&nbsp;</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr{TOGGLE}>
        <td rowspan="2">{THUMB}</td>
        <td>{TITLE}</td>
        <td>{ITEMS}</td>
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
