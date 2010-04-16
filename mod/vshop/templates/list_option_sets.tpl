<!-- BEGIN add_link --><div align="right">{ADD_LINK}</div><!-- END add_link -->
<table width="99%" cellpadding="4">
    <tr>
        <th>{TITLE_HEADER} {TITLE_SORT}</th>
        <th>{TYPE_HEADER} {TYPE_SORT}</th>
        <th>{VALUES_HEADER}</th>
        <th>&nbsp;</th>
    </tr>
<!-- BEGIN listrows -->
    <tr {TOGGLE}>
        <td>{TITLE}</td>
        <td>{TYPE}</td>
        <td>{VALUES}</td>
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
