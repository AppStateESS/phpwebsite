<div class="padded align-right">{ADD_LINK}</div>
<table cellpadding="4" cellspacing="1" width="99%">
    <tr>
        <th>{ID_LABEL} {ID_SORT}</th>
        <th width="65%">{TITLE_LABEL} {TITLE_SORT}</th>
        <th>{ACTION_LABEL}</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr{TOGGLE}>
        <td>{ID}</td>
        <td>{TITLE}</td>
        <td>{ACTION}</td>
    </tr>
    <!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<!-- BEGIN pages -->
<div class="align-center">{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}</div>
<!-- END pages -->
