{TITLE}&#160;{NAV_INFO}
<table border="0" cellspacing="1" cellpadding="4" width="100%">
  <tr>
    <th width="20%">{NAME_SORT}</th>
    <th width="15%">{MEMBERS_LABEL}</th>
    <th>{ACTIONS_LABEL}</th>
  </tr>
  <!-- BEGIN listrows -->
  <tr {TOGGLE}>
    <td>{NAME}</td>
    <td>{MEMBERS}</td>
    <td>{ACTIONS}</td>
  </tr>
  <!-- END listrows -->
</table>
{EMPTY_MESSAGE}
{DEFAULT_SUBMIT}
<br />
<div class="align-center">
{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}
</div>
<div class="align-right">
{SEARCH}
</div>
