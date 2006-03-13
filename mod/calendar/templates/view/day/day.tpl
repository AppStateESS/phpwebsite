<div id="calendar-glance">
<table width="100%">
<!-- BEGIN glance_rows -->
  <tr class="{BLOCK_CLASS}">
    <td>{GLANCE_HOUR}</td>
  </tr>
<!-- END glance_rows -->
</table>
</div>

<div class="bgcolor1 padded"><h1>{TITLE} - {DATE} {PICK}</h1>
    <div class="smaller">{VIEW_LINKS}</div>
</div>
{ADD_EVENT}
<div>{MESSAGE}</div>


<div id="calendar-day-list">

<!-- BEGIN calendar_events -->
  <div class="calendar-event">
    <!-- BEGIN hour --><div class="box-title"><h1>{HOUR}</h1></div><!-- END hour -->
    <div class="calendar-event-title"><h2>{TIME} - {TITLE}</h2></div>
    <div class="calendar-event-summary">{SUMMARY}</div>
    <div class="calendar-admin">{LINKS}</div>
  </div>
<!-- END calendar_events -->
</div>
