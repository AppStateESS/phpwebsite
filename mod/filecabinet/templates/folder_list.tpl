{ADMIN_LINKS}
<table cellpadding="6" cellspacing="1" width="100%">
  <tr>
    <th width="5%">&nbsp;</th>
    <th width="20%">{TITLE_SORT} {TITLE_LABEL}</th>
    <th>{PUBLIC_FOLDER_SORT} {PUBLIC_LABEL}</th>
    <th>{ITEM_LABEL}</th>
    <!-- BEGIN modcreated --><th>{MODULE_CREATED_SORT} {MODULE_CREATED_LABEL}</th><!-- END modcreated -->
    <th>&nbsp;</th>
  </tr>
<!-- BEGIN listrows -->
  <tr {TOGGLE}>
    <td>{ICON}</td>
    <td>{TITLE}</td>
    <td>{PUBLIC}</td>
    <td>{ITEMS}</td>
    <!-- BEGIN mod --><td>{MODULE_CREATED}</td><!-- END mod -->
    <td>{LINKS}</td>
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
