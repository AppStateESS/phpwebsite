<div class="box">
    <div class="box-title">
        <h2 class="text-center">{SCHEDULE_TITLE} - {FULL_MONTH_NAME}, {FULL_YEAR} {DOWNLOAD}</h2>
        <div class="btn-group-wrap">
            <div class="view-links btn-group">{VIEW_LINKS}<br />{SUGGEST}</div>
        </div>
        <div class="align-center smaller">{SCHEDULE_PICK}</div>
    </div>
    <div class="container box-content">{ADD_EVENT} <!-- BEGIN message -->
        <p>{MESSAGE}</p>
        <!-- END message -->
        <div class="details"><!-- BEGIN days -->

        <div class="row clearfix">
         <div class="col-sm-1">
            <p class="date-label">
            <span class="month">{ABBR_WEEKDAY}</span>
			<span class="date-number">{DAY_NUMBER}</span>
			</p>
			</div>
            <!-- BEGIN calendar-events -->
            <div class="col-sm-1"></div>
            <div class="col-sm-11">
            <!-- BEGIN hour -->

            <div class="hour bgcolor2">{HOUR}</div>
            <!-- END hour -->
            <div class="vevent">
                <h3 class="summary">{SUMMARY}</h3>
                <abbr class="dtstart" title="{DTSTART}">{START_TIME}</abbr> <!-- BEGIN end-time -->
                {TO} <abbr class="dtend" title="{DTEND}">{END_TIME}</abbr><!-- END end-time -->
                <!-- BEGIN day-number -->({DAY_NUMBER})<!-- END day-number --> <!-- BEGIN location -->
                <p>{LOCATION_LABEL}: <span class="location">{LOCATION}</span></p>
                <!-- END location -->
                <div class="description">{DESCRIPTION}</div>
                <p class="calendar-admin">{LINKS}</p>
            </div>
            </div>

            <!-- END calendar-events --></div><hr /> <!-- END days --></div>
    </div>
</div>
