{TITLE}&#160;{NAV_INFO}
<table border="0" cellspacing="1" cellpadding="4" width="100%">
  <tr>
    <th width="20%">{GROUPNAME}&#160;{NAME_SORT}</th>
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
{DEFAULT_SUBMIT}
<br />
<!-- BEGIN navigation -->
<div align="center">{NAV_BACKWARD}&#160;{NAV_SECTIONS}&#160;{NAV_FORWARD}<br />{NAV_LIMITS}</div>
<!-- END navigation -->
{SEARCH_LABEL}{SEARCH}
