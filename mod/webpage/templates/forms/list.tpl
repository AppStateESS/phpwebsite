{TOTAL_ROWS}
<table width="100%">
  <tr>
    <th>{TITLE_LABEL} {TITLE_SORT}</th>
    <th>{DATE_CREATED_LABEL} {DATE_CREATED_SORT}</th>
    <th>{DATE_UPDATED_LABEL} {DATE_UPDATED_SORT}</th>
    <th>{CREATED_USER_LABEL} {CREATED_USER_SORT}</th>
    <th>{UPDATED_USER_LABEL} {UPDATED_USER_SORT}</th>
    <th>{ACTION_LABEL}</th>
  </tr>
<!-- BEGIN listrows -->
  <tr>
    <td>{TITLE}</td>
    <td>{DATE_CREATED}</td>
    <td>{DATE_UPDATED}</td>
    <td>{CREATED_USER}</td>
    <td>{UPDATED_USER}</td>
    <td>{ACTION}</td>
  </tr>
<!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<!-- BEGIN navigate -->
<hr />
<div align="center">
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}<br /><br />
{SEARCH}
</div>
<!-- END navigate -->
