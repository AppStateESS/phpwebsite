<div class="box">
<div class="box-title">
<h2 class="text-center"><!-- BEGIN title -->{TITLE} -<!-- END title -->
{FULL_MONTH_NAME}, {FULL_YEAR} {DOWNLOAD}</h2>
<div class="btn-group-wrap">
        <div class="view-links btn-group">{VIEW_LINKS}<br />
            {SUGGEST}</div>
            </div>
<div class="align-center smaller">{SCHEDULE_PICK}</div>
</div>
<div class="box-content">
<div class="month-view-full">
<table>
    <tr>
        <!-- BEGIN calendar-weekdays -->
        <th>{FULL_WEEKDAY}</th>
        <!-- END calendar-weekdays -->
    </tr>
    <!-- BEGIN calendar-row -->
    <tr>
        {CAL_ROW}
        <!-- BEGIN calendar-col -->
        <td class="{CLASS}">
        <div class="cal-day">{DAY}</div>
        <!-- BEGIN calendar-events -->
        <div class="cal-events">{COUNT} <!-- BEGIN show-events -->
        <ul class="event-listing">
            <!-- BEGIN event-list -->
            <li>{EVENT}<br><br></li>
            <!-- END event-list -->
        </ul>
        <!-- END show-events --></div>
        <!-- END calendar-events --></td>
        <!-- END calendar-col -->
    </tr>
    <!-- END calendar-row -->
</table>
</div>
</div>
</div>
