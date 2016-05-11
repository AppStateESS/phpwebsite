<div style="margin-bottom : 1em;">{new} {inactive} {private}</div>
<div style="float:right">{SEARCH}</div>
<table class="table table-striped sans">
    <tr style="vertical-align: top">
        <th style="width : 15%" class="admin-links">{ACTION}</th>
        <th style="width : 35%">{COMPANY_NAME_SORT}</th>
        <th style="width : 25%">{LAST_NAME_SORT}</th>
        <th style="width : 15%">{LAST_LOG_SORT}</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr>
        <td class="admin-icons">{ACTION}</td>
        <td>{COMPANY_NAME}</td>
        <td>{LAST_NAME}<br />{PHONE}</td>
        <td>{LAST_LOG}</td>
    </tr>
    <!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<div class="align-center">{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}</div>
{CSV_REPORT}
