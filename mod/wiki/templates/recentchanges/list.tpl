{BACK}

<table cellpadding="4" cellspacing="1" width="100%">
  <tr>
    <th>{PAGE}</th>
    <th>{UPDATED}</th>
    <th>{EDITOR}</th>
    <th>{COMMENT}</th>
    <th>{VIEW}</th>
  </tr>
<!-- BEGIN listrows -->
  <tr{TOGGLE}>
    <td>{PAGE}</td>
    <td>{UPDATED}</td>
    <td>{EDITOR}</td>
    <td>{COMMENT}</td>
    <td>{VIEW}</td>
  </tr>
<!-- END listrows -->
<!-- BEGIN empty_message -->
  <tr{TOGGLE}>
    <td colspan="5">{EMPTY_MESSAGE}</td>
  </tr>
<!-- END empty_message -->
</table>

<!-- BEGIN navigation -->
<div class="align-center">
{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}
</div>
<!-- END navigation -->
<!-- BEGIN search -->
<div class="align-right">
{SEARCH}
</div>
<!-- END search -->