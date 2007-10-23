{CONTACT_ALERT}
<p>{ADD_ITEM}</p>
<table cellpadding="4" cellspacing="1" width="98%">
  <tr style="vertical-align : top">
    <th width="40%">{TITLE_SORT} {TITLE_LABEL}</th>
    <th>{CREATE_DATE_SORT} {CREATE_DATE_LABEL} {CREATED_NAME_SORT} {NAME_LABEL}
        <br />{UPDATE_DATE_SORT} {UPDATE_DATE_LABEL} {UPDATED_NAME_SORT} {NAME_LABEL}</th>
    <th width="10%">{ACTIVE_SORT} {ACTIVE_LABEL}</th>
    <th width="20%">{ACTION_LABEL}</th>
  </tr>
<!-- BEGIN listrows -->
  <tr {TOGGLE}>
    <td>{TITLE}</td>
    <td class="smaller">{CREATE_DATE} {CREATED_NAME}<br />{UPDATE_DATE} {UPDATED_NAME}</td>
    <td>{ACTIVE}</td>
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
