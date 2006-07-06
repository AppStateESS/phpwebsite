<div id="calendar-glance">
<table width="100%">
<!-- BEGIN glance_rows -->
  <tr class="{BLOCK_CLASS}">
    <td>{GLANCE_HOUR}</td>
  </tr>
<!-- END glance_rows -->
</table>
</div>

<div class="box">
    <div class="box-title">
        <h1 class="align-center">{TITLE} - {DATE} {PICK}</h1>
        <div class="view-links">{VIEW_LINKS}</div>
    </div>
    <div class="box-content">
        {ADD_EVENT}
        <div>{MESSAGE}</div>
        <div id="calendar-day-list">

        <!-- BEGIN calendar_events -->
            <div class="calendar-event">
                <!-- BEGIN hour --><h1 class="calendar-event-hour">{HOUR}</h1><!-- END hour -->
                <h2 class="calendar-event-title">{TITLE} - {TIME}</h2>
                <div class="calendar-event-summary">{SUMMARY}</div>
                <div class="calendar-admin">{LINKS}</div>
            </div>
        <!-- END calendar_events -->
        </div>
    </div>
</div>
