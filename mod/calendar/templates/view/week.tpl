<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    });
</script>

<div class="calendar-view calendar-view-week">
    <div class="view-links btn-group pull-right" role="group">{LEFT_ARROW}{today}{RIGHT_ARROW}</div>
    <div class="btn-group" role="group">
        {GRID}{LIST}{WEEK}
    </div>
    <h2 class="text-center">{SCHEDULE_TITLE} - {DAY_RANGE} {DOWNLOAD}</h2>
    <div>{SCHEDULE_PICK}</div>
    <p>{ADD_EVENT}</p>
    <div class="week-view"> <!-- BEGIN message -->
        <p>{MESSAGE}</p>
        <!-- END message -->
        <!-- BEGIN days -->
        <div class="row clearfix">
            <div class="col-sm-2">
                <div class="date-label">
                    <span class="day">{ABBR_WEEKDAY}</span>
                    <span class="date-number">{DAY_NUMBER}</span>
                </div>
            </div>
            <div class="col-sm-10">
                <!-- BEGIN calendar-events -->
                <!-- BEGIN hour -->
                <div class="hour alert alert-info">{HOUR}</div>
                <!-- END hour -->
                <div class="vevent">
                    <h2 class="summary">{SUMMARY}</h2>
                    <abbr class="dtstart" title="{DTSTART}">{START_TIME}</abbr> <!-- BEGIN end-time -->
                    {TO} <abbr class="dtend" title="{DTEND}">{END_TIME}</abbr><!-- END end-time -->
                    <!-- BEGIN day-number -->({DAY_NUMBER})<!-- END day-number -->
                    <!-- BEGIN location --><p>{LOCATION_LABEL}: <span class="location">{LOCATION}</span></p><!-- END location -->
                    <div class="description">{DESCRIPTION}</div>
                    <div class="admin-icons" style="margin-top:1em">{LINKS}</div>
                </div>
                <!-- END calendar-events -->
            </div>
        </div>
        <!-- END days -->
    </div>
    <div>{SUGGEST}</div>
</div>