<div class="calendar-view calendar-view-day">
    <div class="view-links btn-group pull-right" role="group">{LEFT_ARROW}{today}{RIGHT_ARROW}</div>
    <div class="btn-group" role="group">
        {GRID}{LIST}{WEEK}
    </div>
    <h2 class="text-center">{SCHEDULE_TITLE} - {DATE} {DOWNLOAD}</h2>
    <hr />
    <div class="align-center smaller">{SCHEDULE_PICK}</div>

    <div class="">{ADD_EVENT}
        <!-- BEGIN message --><p>{MESSAGE}</p><!-- END message -->
        <div id="day-view"><!-- BEGIN calendar-events --> <!-- BEGIN hour -->
            <h3 class="hour">{HOUR}</h3>
            <!-- END hour -->
            <div class="vevent">
                <h4 class="summary">{SUMMARY}</h4>
                <abbr class="dtstart" title="{DTSTART}">{START_TIME}</abbr> <!-- BEGIN end-time -->{TO}
                <abbr class="dtend" title="{DTEND}">{END_TIME}</abbr><!-- END end-time -->
                <!-- BEGIN day-number -->({DAY_NUMBER})<!-- END day-number --> <!-- BEGIN location -->
                <p>{LOCATION_LABEL}: <span class="location">{LOCATION}</span></p>
                <!-- END location -->
                <div class="description">{DESCRIPTION}</div>
                <p class="calendar-admin">{LINKS}</p>
            </div>
            <!-- END calendar-events -->
        </div>
    </div>
    <div>{SUGGEST}</div>
</div>