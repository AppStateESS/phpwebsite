<div style="margin-bottom:1em">{new}</div>
<table class="table table-striped">
    <tr style="vertical-align: top">
        <th style="width : 100px">&#160;</th>
        <th>{NAME_SORT}</th>
        <th>{COMPANY_NAME_SORT}</th>
        <th>{TIMEOUT_SORT}</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr{TOGGLE}>
        <td class="admin-icons">{ACTION}</td>
        <td>{NAME}</td>
        <td>{COMPANY_NAME}</td>
        <td>{TIMEOUT}</td>
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