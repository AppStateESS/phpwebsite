<div class="box">
    <div class="box-title">
        <h1 class="align-center">{SCHEDULE_TITLE} -
        {DAY_RANGE} {PICK}</h1>
        <div class="view-links">{VIEW_LINKS}</div>
    </div>
    <div class="box-content">

        <div class="month-view-list">
<!-- BEGIN message -->{MESSAGE}<!-- END message -->
<!-- BEGIN days -->
            <div class="day bgcolor1">{FULL_WEEKDAY} {DAY_NUMBER}</div>
<!-- BEGIN hours -->
            <div class="hour bgcolor2">{HOUR_12}{AM_PM}</div>
<!-- BEGIN events -->
            <div class="list-event">
                <span class="event-title">{TITLE}</span> |
                <span class="event-time">{TIME}</span>
                <div class="event-summary">{SUMMARY}</div>
            </div>
<!-- END events -->
<!-- END hours -->
<!-- END days -->
        </div>
    </div>
</div>
