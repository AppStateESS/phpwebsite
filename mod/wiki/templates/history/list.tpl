{BACK}

<h1>{TITLE}</h1>

<table cellpadding="4" cellspacing="1" width="100%">
  <tr>
    <th>{VERSION}</th>
    <th>{UPDATED}</th>
    <th>{EDITOR}</th>
    <th>{COMMENT}</th>
    <th>{DIFF}</th>
    <th>{ACTIONS}</th>
  </tr>
<!-- BEGIN listrows -->
  <tr{TOGGLE}>
    <td>{VERSION}</td>
    <td>{UPDATED}</td>
    <td>{EDITOR}</td>
    <td>{COMMENT}</td>
    <td>{DIFF}</td>
    <td>{ACTIONS}</td>
  </tr>
<!-- END listrows -->
<!-- BEGIN empty_message -->
  <tr{TOGGLE}>
    <td colspan="6">{EMPTY_MESSAGE}</td>
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