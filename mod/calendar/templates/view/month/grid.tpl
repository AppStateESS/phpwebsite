<div class="calendar-view calendar-view-month-list">
    <div class="view-links btn-group pull-right" role="group">{LEFT_ARROW}{today}{RIGHT_ARROW}</div>
    <div class="btn-group" role="group">
        {GRID}{LIST}{WEEK}
    </div>
    <h2 class="text-center">{TITLE} - {FULL_MONTH_NAME}, {FULL_YEAR} {DOWNLOAD}</h2>
    <div>{SCHEDULE_PICK}</div>
    <p>{ADD_EVENT}</p>
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
                            <li><small>{EVENT}</small></li>
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
