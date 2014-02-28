{new} | {email}

<table class="table table-striped">
    <tr style="vertical-align: top">
        <th width="25%">{COMPANY_NAME_SORT}</th>
        <th width="20%">{LAST_NAME_SORT}<br />{EMAIL_ADDRESS_SORT}</th>
        <th>{LAST_LOG_SORT}</th>
        <th class="admin-links">{ACTION}</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr{TOGGLE}>
        <td>{COMPANY_NAME}</td>
        <td>{LAST_NAME}<br />{PHONE}</td>
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
