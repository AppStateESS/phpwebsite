<div style="float:right">{SEARCH}</div>
<div style="margin-bottom:1em">{new}</div>
<table class="table table-striped sans">
    <tr>
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

{CSV_REPORT}
<div id="photo-form" style="display : none"></div>