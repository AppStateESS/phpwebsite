<div style="margin-bottom : 1em">{new} {inactive}</div>

<table class="table table-striped">
    <tr style="vertical-align: top">
        <th style="width : 30%">{COMPANY_NAME_SORT}</th>
        <th style="width : 30%">{LAST_NAME_SORT}</th>
        <th style="width : 15%">{LAST_LOG_SORT}</th>
        <th style="width : 15%" class="admin-links">{ACTION}</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr{TOGGLE}>
        <td>{COMPANY_NAME}</td>
        <td>{LAST_NAME} {PHONE}</td>
        <td>{LAST_LOG}</td>
        <td class="admin-icons">{ACTION}</td>
    </tr>
    <!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<div class="align-center">{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}</div>
<div class="align-right">{SEARCH}</div>
{CSV_REPORT}
