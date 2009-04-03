{PRINT} | {CSV} | {SLOT_LISTING} | {EMAIL}
<table cellpadding="5" width="100%">
  <tr>
    <th>{LAST_NAME_SORT}</th>
    <th>{FIRST_NAME_SORT}</th>
    <th>
      {EMAIL_SORT}<br />
      {PHONE_SORT}
    </th>
    <th>{EXTRA_LABEL}</th>
  </tr>
  <!-- BEGIN listrows -->
  <tr>
    <td>{LAST_NAME}</td>
    <td>{FIRST_NAME}</td>
    <td>{EMAIL}<br />{PHONE}</td>
    <td>
      <!-- BEGIN extra1 --><div>{EXTRA1}</div><!-- END extra1 -->
      <!-- BEGIN extra2 --><div>{EXTRA2}</div><!-- END extra2 -->
      <!-- BEGIN extra3 --><div>{EXTRA3}</div><!-- END extra3 -->
    </td>
  </tr>
  <!-- END listrows -->
</table>
<div align="center">
  <b>{PAGE_LABEL}</b><br />
  {PAGES}<br />
  {LIMITS}
</div>
{SEARCH}
