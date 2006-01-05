<script type="text/javascript">
//<![CDATA[

function check_start_date() {
  form = document.getElementById('event_form');

  start_date = form.event_form_start_date.value;
  end_date = form.event_form_end_date.value;

  if (start_date > end_date) {
     form.event_form_end_date.value = start_date;
  }

  start_time = form.event_form_start_time_hour.value - 0;
  end_time   = form.event_form_end_time_hour.value - 0;

  if (start_time > end_time) {
     form.event_form_end_time_hour.value = start_time;
  }
}

function check_end_date() {
  form = document.getElementById('event_form');

  start_date = form.event_form_start_date.value;
  end_date = form.event_form_end_date.value;

  if (start_date > end_date) {
     form.event_form_start_date.value = end_date;
  }

  start_time = form.event_form_start_time_hour.value - 0;
  end_time   = form.event_form_end_time_hour.value - 0;

  if (start_time > end_time) {
     form.event_form_start_time_hour.value = end_time;
  }
}

function alter_date(event_type) {
  start_time_display = document.getElementById('start-time');
  end_time_display = document.getElementById('end-time');

  switch(event_type.value) {
     case '1':
       start_time_display.style.display = '';
       end_time_display.style.display = '';
     break;

     case '2':
       start_time_display.style.display = 'none';
       end_time_display.style.display = 'none';
     break;

     case '3':
       start_time_display.style.display = '';
       end_time_display.style.display = 'none';
     break;

     case '4':
       start_time_display.style.display = 'none';
       end_time_display.style.display = '';
     break;
  }
  
}

//]]>
</script>

<!-- BEGIN error --><h2 class="error">{ERROR}</h2><!-- END error -->

{START_FORM}
<table class="form-table" width="98%">
  <tr>
    <td class="label" width="100px">{TITLE_LABEL}</td>
    <td>{TITLE}</td>
  </tr>
  <tr>
    <td class="label">{SUMMARY_LABEL}</td>
    <td>{SUMMARY}</td>
  </tr>
  <tr>
    <td class="label">{EVENT_TYPE_LABEL}</td>
    <td>
      {EVENT_TYPE_1} {EVENT_TYPE_1_LABEL}<br />
      {EVENT_TYPE_2} {EVENT_TYPE_2_LABEL}<br />
      {EVENT_TYPE_3} {EVENT_TYPE_3_LABEL}<br />
      {EVENT_TYPE_4} {EVENT_TYPE_4_LABEL}
    </td>
  </tr>
  <tr>
    <td class="label">{START_DATE_LABEL}<br /><span style="font-weight:normal" class="smaller">YYYY/MM/DD</td>
    <td>{START_DATE} {START_CAL} <span id="start-time">{START_TIME_HOUR}:{START_TIME_MINUTE}</span></td>
  </tr>
  <tr>
    <td class="label">{END_DATE_LABEL}<br /><span style="font-weight:normal" class="smaller">YYYY/MM/DD</span></td>
    <td>{END_DATE} {END_CAL} <span id="end-time">{END_TIME_HOUR}:{END_TIME_MINUTE}</span></td>
  </tr>
</table>
{SUBMIT}
<div class="align-right">{CLOSE}</div>
{END_FORM}
