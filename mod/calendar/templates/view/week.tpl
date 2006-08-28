<div class="box">
    <div class="box-title">
        <h1 class="align-center">{SCHEDULE_TITLE} - {DAY_RANGE} {PICK}</h1>
        <div class="view-links">{VIEW_LINKS}</div>
        <div class="align-center smaller">{SCHEDULE_PICK}</div>
    </div>
    <div class="box-content">
    {MESSAGE}
        <div class="week-view">
<!-- BEGIN message -->{MESSAGE}<!-- END message -->
<!-- BEGIN days -->
            <div class="day bgcolor1">{FULL_WEEKDAY} {DAY_NUMBER}</div>
<!-- BEGIN hours -->
            <div class="hour bgcolor2">{HOUR_12}{AM_PM}</div>
<!-- BEGIN events -->
            <div class="calendar-event">
                <span class="event-title">{TITLE}</span> |
                <span class="event-time">{TIME}</span>
                <div class="event-summary">{SUMMARY}</div>
                <p class="calendar-admin">{LINKS}</p>
            </div>
<!-- END events -->
<!-- END hours -->
<!-- END days -->
        </div>
    </div>
</div>
