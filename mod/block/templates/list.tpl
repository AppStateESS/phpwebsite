<table cellpadding="4" cellspacing="1" width="100%">
  <tr>
    <th><b>{TITLE}</b> {TITLE_SORT}</th>
    <th><b>{CONTENT}</b></th>
    <th><b>{ACTION}</b></th>
  </tr>
<!-- BEGIN listrows -->
  <tr {TOGGLE}>
    <td>{TITLE}</td>
    <td>{CONTENT}</td>
    <td>{ACTION}</td>
  </tr>
<!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<div style="text-align : center">
{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}
</div>
<div style="text-align : right">
{SEARCH}
</div>
