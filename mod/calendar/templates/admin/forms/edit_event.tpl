<script type="text/javascript">
  $(window).load(function () {
    $('#event_form_start_date').datetimepicker({timepicker: 0, format: 'Y/m/d'});
    $('#event_form_end_date').datetimepicker({timepicker: 0, format: 'Y/m/d'});
    $('#event_form_end_repeat_date').datetimepicker({timepicker: 0, format: 'Y/m/d'});
  });
</script>
<!-- BEGIN hr -->
<!-- BEGIN event-tab -->
<div class="event-tabs">
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" id="event-pick" class="active"><a href="javascript:changeTab(0)">{EVENT_TAB}</a></li>
    <li role="presentation" id="repeat-pick" class="inactive"><a href="javascript:changeTab(1)">{REPEAT_TAB}</a></li>
  </ul>
  <!-- BEGIN repeat-warning -->
  <div style="margin: 10px">{REPEAT_WARNING}</div>
  <!-- END repeat-warning --></div>
<!-- END event-tab -->
<div class="alert alert-danger" style="display:none" id="event-error">{ERROR}</div>
{START_FORM}
<input id="event-view" type="hidden" name="view" value="" />
<div id="event-tab" style="display: block">
  <table class="table table-striped">
    <tr>
      <td>{SUMMARY_LABEL}</td><td>{SUMMARY}</td>
    </tr>
    <tr>
      <td>{START_DATE_LABEL} 
      </td><td>{START_DATE} <span id="start-time" style="display: inline">
          {START_TIME_HOUR}:{START_TIME_MINUTE}</span> <small>(YYYY/MM/DD)</small></td>
    </tr>
    <tr>
      <td>{END_DATE_LABEL}</td>
      <td>{END_DATE} <span id="end-time" style="display: inline">
          {END_TIME_HOUR}:{END_TIME_MINUTE}</span> <small>(YYYY/MM/DD)</small></td>
    </tr>
    <tr>
      <td>{ALL_DAY}
        {ALL_DAY_LABEL}</td>
      <td>{SHOW_BUSY}
        {SHOW_BUSY_LABEL}</td>
    </tr>
    <tr>
      <td>{LOCATION_LABEL}</td><td>{LOCATION}</td>
    </tr>
    <tr>
      <td>{LOC_LINK_LABEL}</td><td>{LOC_LINK}</td>
    </tr>
    <tr>
      <td colspan="2">{DESCRIPTION_LABEL}<br />{DESCRIPTION}</td>
    </tr>
  </table>
</div>
<div id="repeat-tab" style="display: none">{REPEAT_EVENT}
  {REPEAT_EVENT_LABEL}
  <table class="table table-striped">
    <tr>
      <td>{END_REPEAT_DATE_LABEL}</td>
      <td>{END_REPEAT_DATE} {END_REPEAT}</td>
    </tr>
    <tr class="bgcolor2">
      <td>{REPEAT_MODE_1} {REPEAT_MODE_1_LABEL}</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>{REPEAT_MODE_2} {REPEAT_MODE_2_LABEL}</td>
      <td><span>{WEEKDAY_REPEAT_1}
          {WEEKDAY_REPEAT_1_LABEL}</span> <span>{WEEKDAY_REPEAT_2}
          {WEEKDAY_REPEAT_2_LABEL}</span> <span>{WEEKDAY_REPEAT_3}
          {WEEKDAY_REPEAT_3_LABEL}</span> <span>{WEEKDAY_REPEAT_4}
          {WEEKDAY_REPEAT_4_LABEL}</span> <br />
        <span>{WEEKDAY_REPEAT_5} {WEEKDAY_REPEAT_5_LABEL}</span> <span>{WEEKDAY_REPEAT_6}
          {WEEKDAY_REPEAT_6_LABEL}</span> <span>{WEEKDAY_REPEAT_7}
          {WEEKDAY_REPEAT_7_LABEL}</span></td>
    </tr>
    <tr class="bgcolor2">
      <td>{REPEAT_MODE_3} {REPEAT_MODE_3_LABEL}</td>
      <td>{MONTHLY_REPEAT}</td>
    </tr>
    <tr>
      <td>{REPEAT_MODE_4} {REPEAT_MODE_4_LABEL}</td>
      <td></td>
    </tr>
    <tr class="bgcolor2">
      <td>{REPEAT_MODE_5} {REPEAT_MODE_5_LABEL}</td>
      <td>{EVERY_REPEAT_NUMBER} {EVERY_REPEAT_WEEKDAY}
        {EVERY_REPEAT_FREQUENCY}</td>
    </tr>
  </table>
</div>
<!-- <div>{SAVE}{SAVE_SOURCE}{SAVE_COPY} {SYNC}</div> -->
{END_FORM}
