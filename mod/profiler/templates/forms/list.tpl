<table cellpadding="4" cellspacing="1" width="99%">
    <tr>
        <th>{LASTNAME} {LASTNAME_SORT}</th>
        <th>{FIRSTNAME} {FIRSTNAME_SORT}</th>
        <th>{PROFILE_TYPE} {PROFILE_TYPE_SORT}</th>
        <th>{SUBMIT_DATE} {SUBMIT_DATE_SORT}</th>
        <th>{ACTION}</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr{TOGGLE}>
        <td>{LASTNAME}</td>
        <td>{FIRSTNAME}</td>
        <td>{PROFILE_TYPE}</td>
        <td>{SUBMIT_DATE}</td>
        <td>{ACTION}</td>
    </tr>
    <!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<div class="align-center">{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}</div>
<div class="align-right">{SEARCH}</div>
