window.onload = function()
{
    all_day = document.getElementById('event_form_all_day');
    alter_date(all_day);
}



function check_start_date() {
    form = document.getElementById('event_form');

    start_date = form.event_form_start_date.value;
    end_date = form.event_form_end_date.value;

    if (start_date > end_date) {
        form.event_form_end_date.value = start_date;
    } else if (start_date < end_date) {
        link = document.getElementById('sync-dates');
        link.style.display = 'inline';
    }

    start_time = form.event_form_start_time_hour.value - 0;
    end_time = form.event_form_end_time_hour.value - 0;

    if (start_date == end_date && start_time > end_time) {
        form.event_form_end_time_hour.value = start_time;
    }
}

function check_end_date() {
    form = document.getElementById('event_form');

    start_date = form.event_form_start_date.value;
    end_date = form.event_form_end_date.value;

    if (start_date > end_date) {
        form.event_form_start_date.value = end_date;
    } else if (start_date < end_date) {
        link = document.getElementById('sync-dates');
        link.style.display = 'inline';
    }


    start_time = form.event_form_start_time_hour.value - 0;
    end_time = form.event_form_end_time_hour.value - 0;

    if (start_date == end_date && start_time > end_time) {
        form.event_form_start_time_hour.value = end_time;
    }
}

function alter_date(all_day) {
    start_time = document.getElementById('start-time');
    end_time = document.getElementById('end-time');

    if (all_day.checked) {
        start_time.style.display = 'none';
        end_time.style.display = 'none';
    } else {
        start_time.style.display = 'inline';
        end_time.style.display = 'inline';
    }

}

function sync_dates()
{
    form = document.getElementById('event_form');
    start_date = form.event_form_start_date.value;
    end_date = form.event_form_end_date.value;
    form.event_form_end_date.value = start_date;
    link = document.getElementById('sync-dates');
    link.style.display = 'none';

}
