<div style="text-align: right">{SEARCH}</div>
<p>{TOTAL_ROWS} | {LINK}</p>
<table width="100%" cellpadding="4" style="border-collapse: collapse"
    border="1"
>
    <tr>
        <th>{CREATED_SORT}</th>
        <th>{START_ADDRESS_SORT}</th>
        <th>{DEST_ADDRESS_SORT}</th>
        <th>&#160;</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr class="{TOGGLE}">
        <td>{CREATED}</td>
        <td>{START_ADDRESS}</td>
        <td>{DEST_ADDRESS}</td>
        <td>{LINKS}</td>
    </tr>
    <!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<div align="center"><b>{PAGE_LABEL}</b><br />
{PAGES}<br />
{LIMITS}</div>
