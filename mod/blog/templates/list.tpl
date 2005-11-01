<table cellpadding="4" cellspacing="1" width="100%">
  <tr>
    <th>{TITLE} {TITLE_SORT}</th>
    <th>{ENTRY}</th>
    <th>{DATE} {DATE_SORT}</th>
    <th>{ACTION}</th>
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
{EMPTY_MESSAGE}
<div class="align-center">
{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}
</div>
<div class="align-right">
{SEARCH}
</div>
