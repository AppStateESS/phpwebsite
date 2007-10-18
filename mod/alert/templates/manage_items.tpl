<p>{ADD_ITEM}</p>
<table cellpadding="4" cellspacing="1" width="98%">
  <tr style="vertical-align : top">
    <th>{TITLE_SORT} {TITLE_LABEL}</th>
    <th></th>
    <th>{ACTIVE_SORT} {ACTIVE_LABEL}</th>
    <th width="20%">{ACTION_LABEL}</th>
  </tr>
<!-- BEGIN listrows -->
  <tr {TOGGLE}>
    <td>{TITLE}</td>
    <td></td>
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
