<h1>WhoDis?</h1>
<div class="align-right">
{START_FORM}
{PURGE_LABEL} {DAYS_OLD} {VISIT_LIMIT_LABEL} {VISIT_LIMIT} {SUBMIT}
{END_FORM}
</div>
<table width="99%" cellpadding="4">
  <tr>
    <th>{URL_LABEL} {URL_SORT}</th>
    <th>{CREATED_LABEL} {CREATED_SORT}</th>
    <th>{UPDATED_LABEL} {UPDATED_SORT}</th>
    <th>{VISITS_LABEL} {VISITS_SORT}</th>
  </tr>
<!-- BEGIN listrows -->
  <tr>
    <td>{URL}</td>
    <td>{CREATED}</td>
    <td>{UPDATED}</td>
    <td>{VISITS}</td>
  </tr>
<!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<div align="center">
  <b>{PAGE_LABEL}</b><br />
  {PAGES}<br />
  {LIMITS}
</div>
