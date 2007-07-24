{PRINT} | {CSV} | {SLOT_LISTING}
<table cellpadding="5" width="100%">
  <tr>
    <th>{LAST_NAME_SORT} {LAST_NAME_LABEL}</th>
    <th>{FIRST_NAME_SORT} {FIRST_NAME_LABEL}</th>
    <th>{PHONE_LABEL}</th>
    <th>{EMAIL_SORT} {EMAIL_LABEL}</th>
    <th>{ORGANIZATION_SORT} {ORGANIZATION_LABEL}</th>
  </tr>
  <!-- BEGIN listrows -->
  <tr>
    <td>{LAST_NAME}</td>
    <td>{FIRST_NAME}</td>
    <td>{PHONE}</td>
    <td>{EMAIL}</td>
    <td>{ORGANIZATION}</td>
  </tr>
  <!-- END listrows -->
</table>
<div align="center">
  <b>{PAGE_LABEL}</b><br />
  {PAGES}<br />
  {LIMITS}
</div>
{SEARCH}
