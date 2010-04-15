{ALPHA_CLICK}
<!-- BEGIN add_link --><div align="right">{ADD_LINK}</div><!-- END add_link -->
<table width="99%" cellpadding="4">
    <tr>
        <th>{TITLE_HEADER} {TITLE_SORT}</th>
        <th>{TYPE_HEADER} {TYPE_SORT}</th>
        <th>{SORT_HEADER} {SORT_SORT}</th>
        <th>{LIST_HEADER} {LIST_SORT}</th>
        <th>{SEARCH_HEADER} {SEARCH_SORT}</th>
        <th>{PRIVATE_HEADER} {PRIVATE_SORT}</th>
        <th>&nbsp;</th>
    </tr>
<!-- BEGIN listrows -->
    <tr {TOGGLE}>
        <td nowrap="nowrap">{TITLE}</td>
        <td>{TYPE}</td>
        <td>{SORT}</td>
        <td>{LIST}</td>
        <td>{SEARCH}</td>
        <td>{PRIVATE}</td>
        <td>{ACTION}</td>
    </tr>
<!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<div class="align-right">
    {ADD_FORM}
</div>
<div class="align-center">
    {TOTAL_ROWS}<br />
    {PAGE_LABEL} {PAGES}<br />
    {LIMIT_LABEL} {LIMITS}
</div>
<div class="align-right">
    {SEARCH}
</div>
