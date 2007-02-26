<table cellpadding="4" cellspacing="1" width="98%">
  <tr>
    <th>{TITLE} {TITLE_SORT}</th>
    <th>{SUMMARY}</th>
    <th>{DATE} {CREATE_DATE_SORT}</th>
    <th>{ACTION}</th>
  </tr>
<!-- BEGIN listrows -->
  <tr {TOGGLE}>
    <td>{TITLE}</td>
    <td>{SUMMARY}</td>
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
