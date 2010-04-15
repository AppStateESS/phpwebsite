<table width="99%" cellpadding="4">
    <tr>
        <th>{LABEL_SORT} &nbsp; {SUBJECT_SORT}</th>
        <!-- BEGIN address_sort --><th>{ADDRESS_SORT}</th><!-- END address_sort -->
        <th>&nbsp;</th>
    </tr>
<!-- BEGIN listrows -->
    <tr {TOGGLE}>
        <td>{LABEL}</td>
        <!-- BEGIN address --><td>{ADDRESS}</td><!-- END address -->
        <td>{ACTION}</td>
    </tr>
    <tr {TOGGLE}>
        <td colspan="3" class="smaller">{SUBJECT}</td>
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
