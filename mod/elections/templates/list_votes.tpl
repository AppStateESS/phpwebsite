<table width="99%" cellpadding="4">
    <tr>
        <th>{BALLOT_HEADER} {BALLOT_ID_SORT}</th>
        <th>{USER_HEADER} {USERNAME_SORT}</th>
        <th>{DATE_HEADER} {VOTEDATE_SORT}</th>
        <th>{IP_HEADER} {IP_SORT}</th>
        <th>&nbsp;</th>
    </tr>
<!-- BEGIN listrows -->
    <tr {TOGGLE}>
        <td>{BALLOT}</td>
        <td>{USER}</td>
        <td>{DATE}</td>
        <td>{IP}</td>
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
