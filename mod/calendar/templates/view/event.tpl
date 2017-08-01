<div class="box">
    <div class="btn-group-wrap">
        <div class="view-links btn-group">{today}{DAY_LINK}{WEEK}{GRID}{LIST}<br />{SUGGEST}</div>
    </div>
    <div class="align-center smaller">{SCHEDULE_PICK}</div>
</div>
    <div class="vevent">
        <div class="box-title">
            <h2 class="summary">{SUMMARY} {DOWNLOAD}</h2>
            <h3 class="timestamp"><abbr class="dtstart" title="{DTSTART}">{START_TIME}</abbr>
                <!-- BEGIN end-time -->&ndash; <abbr class="dtend" title="{DTEND}">{END_TIME}</abbr><!-- END end-time -->
            </h3>
        </div>
        <div class="box-content"><!-- BEGIN location -->
            <p class="location"><strong>{LOCATION_LABEL}:</strong> {LOCATION}</p>
            <!-- END location -->
            <div class="description">{DESCRIPTION}</div>
            <div class="align-right"><!-- BEGIN admin -->{LINKS} | <!-- END admin -->{BACK_LINK}</div>
        </div>
    </div>
</div>
<!-- BEGIN close-window -->
<div class="align-center">{CLOSE_WINDOW}</div>
<!-- END close-window -->
