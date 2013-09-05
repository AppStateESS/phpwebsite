<table class="table table-striped table-hover">
    <tr style="vertical-align: top">
        <th>{NAME_SORT}</th>
        <th>{TYPE_SORT}</th>
        <th>{ACTIVE_SORT}</th>
        <th>{ACTION}</th>
    </tr>
<!-- BEGIN listrows -->
    <tr {TOGGLE}>
        <td>{NAME}</td>
        <td>{TYPE}</td>
        <td>{ACTIVE}</td>
        <td>{ACTION}</td>
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
{SEARCH}
</div>
{CSV_REPORT}
