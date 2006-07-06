<div class="box">
  <div class="box-title"><h1 class="align-center">{TITLE} -
      {FULL_MONTH_NAME}, {FULL_YEAR} {PICK}</h1>
      <div class="view-links">{VIEW_LINKS}</div>
  </div>
  <div class="box-content">
  <div class="month-view-full">
    <table>
      <tr>
        <!-- BEGIN calendar-weekdays --><th>{FULL_WEEKDAY}</th><!-- END calendar-weekdays -->
      </tr>
    <!-- BEGIN calendar-row -->
      <tr>
      {CAL_ROW}
      <!-- BEGIN calendar-col --><td class="{CLASS}"><div class="cal-day">{DAY}</div><div class="cal-events">{COUNT}</div></td><!-- END calendar-col -->
      </tr>
    <!-- END calendar-row -->
    </table>
  </div>
  </div>
</div>
