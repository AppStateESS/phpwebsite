<table cellpadding="4" cellspacing="1" width="100%">
  <tr>
    <th><b>{TITLE}</b> {TITLE_SORT}</th>
    <th><b>{ENTRY}</b></th>
    <th><b>{DATE}</b> {DATE_SORT}</th>
    <th><b>{ACTION}</b></th>
  </tr>
<!-- BEGIN listrows -->
  <tr {TOGGLE}>
    <td>{TITLE}</td>
    <td>{ENTRY}</td>
    <td>{DATE}</td>
    <td>{ACTION}</td>
  </tr>
<!-- END listrows -->
</table>
<div style="text-align : center">
{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}
</div>
<div style="text-align : right">
{SEARCH}
</div>
