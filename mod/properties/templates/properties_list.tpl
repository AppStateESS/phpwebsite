{new}
<table class="table table-striped">
    <tr style="vertical-align: top">
        <th>{NAME_SORT}</th>
        <th>{COMPANY_NAME_SORT}</th>
        <th>{TIMEOUT_SORT}</th>
        <th style="width : 100px">&#160;</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr{TOGGLE}>
        <td>{NAME}</td>
        <td>{COMPANY_NAME}</td>
        <td>{TIMEOUT}</td>
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
<div id="photo-form" style="display : none"></div>