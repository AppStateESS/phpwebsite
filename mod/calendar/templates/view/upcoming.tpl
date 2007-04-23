<div class="upcoming-schedule">
   <h2>{TITLE}</h2>
   <hr />
   <!-- BEGIN events -->
   <div class="vevent">
      <span class="summary">{SUMMARY}</span><br />
      <abbr class="dtstart" title="{DTSTART}">{START_TIME}</abbr>
      <!-- BEGIN end-time -->{TO} <abbr class="dtend" title="{DTEND}">{END_TIME}</abbr><!-- END end-time -->
      <!-- BEGIN day-number -->({DAY_NUMBER})<!-- END day-number -->
      <!-- BEGIN location --><p>{LOCATION_LABEL}: <span class="location">{LOCATION}</span></p><!-- END location -->
   </div>
   <!-- END events -->
</div>
