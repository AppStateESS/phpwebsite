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
