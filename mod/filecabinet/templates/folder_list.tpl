{ADMIN_LINKS}
<table cellpadding="6" cellspacing="2" width="100%">
  <tr>
    <th>{TITLE_SORT}</th>
    <th width="20%">{PUBLIC_FOLDER_SORT}</th>
    <th width="10%">{ITEM_LABEL}</th>
    <!-- BEGIN modcreated --><th>{MODULE_CREATED_SORT}</th><!-- END modcreated -->
    <th width="20%">&#160;</th>
  </tr>
<!-- BEGIN listrows -->
  <tr class="{TOGGLE}">
    <td>{TITLE}</td>
    <td>{PUBLIC}</td>
    <td>{ITEMS}</td>
    <!-- BEGIN mod --><td>{MODULE_CREATED}</td><!-- END mod -->
    <td><ul id="fc-fldr-nav">{LINKS}</ul></td>
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
{FILE_SEARCH}
</div>
