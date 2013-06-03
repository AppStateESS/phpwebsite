<div class="box">
    <div class="box-title">
        <h2 class="align-center">{SCHEDULE_TITLE} - {DAY_RANGE} {PICK}
            {DOWNLOAD}</h2>
        <div class="view-links">{VIEW_LINKS}<br />
            {SUGGEST}</div>
        <div class="align-center smaller">{SCHEDULE_PICK}</div>
    </div>
    <div class="box-content">
        <div class="week-view">{ADD_EVENT} <!-- BEGIN message -->
            <p>{MESSAGE}</p>
            <!-- END message --> <!-- BEGIN days -->
            <div class="day bgcolor1">{FULL_WEEKDAY} {DAY_NUMBER}</div>
            <!-- BEGIN calendar-events --> <!-- BEGIN hour -->
            <div class="hour bgcolor2">{HOUR}</div>
            <!-- END hour -->
            <div class="vevent">
                <h2 class="summary">{SUMMARY}</h2>
                <abbr class="dtstart" title="{DTSTART}">{START_TIME}</abbr> <!-- BEGIN end-time -->
                {TO} <abbr class="dtend" title="{DTEND}">{END_TIME}</abbr><!-- END end-time -->
                <!-- BEGIN day-number -->({DAY_NUMBER})<!-- END day-number --> <!-- BEGIN location -->
                <p>{LOCATION_LABEL}: <span class="location">{LOCATION}</span></p>
                <!-- END location -->
                <div class="description">{DESCRIPTION}</div>
                <p class="calendar-admin">{LINKS}</p>
            </div>
            <!-- END calendar-events --> <!-- END days -->
        </div>
    </div>
</div>
