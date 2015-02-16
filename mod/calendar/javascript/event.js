getDate = function (d) {
    var sd = new Date(d * 1000);
    var sd_d = sd.getDate();
    if (sd_d < 10) {
        sd_d = '0' + sd_d;
    }
    var sd_m = sd.getMonth() + 1;
    if (sd_m < 10) {
        sd_m = '0' + sd_m;
    }
    return sd.getFullYear() + '/' + sd_m + '/' + sd_d
};

errorCheckEventForm = function () {
    var error = false;
    var start_date = $('#event_form_start_date').val();
    var end_date = $('#event_form_end_date').val();
    var error_message = new Array();

    if ($('#event_form_summary').val().length == 0) {
        error = true;
        error_message.push('Summary is empty');
    }

    // If this is not an all day event, we have to be aware of times.
    if (!$('#event_form_all_day').is(':checked')) {
        if ($('#event_form_start_time_hour').val() == $('#event_form_end_time_hour').val()) {
            if ($('#event_form_start_time_minute').val() == $('#event_form_end_time_minute').val()) {
                error_message.push('Start and end time may not be the same.');
                error = true;
            } else if ($('#event_form_start_time_minute').val() > $('#event_form_end_time_minute').val()) {
                error_message.push('End time is before start time.');
                error = true;
            }
        }
    }

    if ($('#event_form_repeat_event').is(':checked')) {
        if ($('#event_form_end_repeat_date').val() < end_date) {
            error_message.push('Repeat date must be after event date');
            error = true;
        }
    }

    if (error) {
        $('#event-error').html(error_message.join('<br />'));
        $('#event-error').show();
        return false;
    } else {
        return true;
    }
};

setEventCKEDITOR = function (content) {
    CKEDITOR.instances['event_form_description'].updateElement();
    CKEDITOR.instances['event_form_description'].setData(content);
};

$(document).ready(function () {
    $('.add-event').click(function () {
        resetEventForm();
        var schedule_id = $(this).data('schedule-id');
        var current_date = $(this).data('date');
        var start_date = getDate(current_date);
        var end_date = getDate(current_date);
        var view = $(this).data('view');

        if (typeof view !== 'undefined') {
            $('#event-view').val(view);
        } else {
            $('#event-view').val('');
        }

        $('#event-pick').removeClass('inactive');
        $('#event-pick').addClass('active');
        $('#repeat-pick').removeClass('active');
        $('#repeat-pick').addClass('inactive');

        $('#event_form_start_date').val(start_date);
        $('#event_form_end_date').val(end_date);
        $('event_form_sch_id').val(schedule_id);
        $('#edit-event').modal('show');
    });

    $('.edit-event').click(function () {
        resetEventForm();
        var event_id = $(this).data('eventId');
        var schedule_id = $(this).data('scheduleId');
        var view = $(this).data('view');
        if (typeof view !== 'undefined') {
            $('#event-view').val(view);
        } else {
            $('#event-view').val('');
        }

        $.get('index.php', {
            module: 'calendar',
            aop: 'get_event_json',
            'event_id': event_id,
            'schedule_id': schedule_id
        }, function (data) {
            fillEventForm(data);
        }, 'json').always(function () {
            $('#edit-event').modal('show');
        });
    });

    $('#submit-event').click(function () {
        if (errorCheckEventForm()) {
            $('#event_form').submit();
        }
    });
});

function fillEventForm(data)
{
    $('#event_form_event_id').val(data.event_id);
    $('#event_form_summary').val(data.summary);
    $('#event_form_location').val(data.location);
    $('#event_form_loc_link').val(data.loc_link);
    $('#event_form_description').html(data.description);
    setEventCKEDITOR(data.description);
    $('#event_form_start_date').val(data.start_date);
    $('#event_form_end_date').val(data.end_date);
    if (data.all_day == 1) {
        $('#event_form_all_day').prop('checked', true);
        $('#start-time').hide();
        $('#end-time').hide();
    } else {
        $('#start-time').show();
        $('#end-time').show();
        $('#event_form_all_day').prop('checked', false);
    }
    if (data.show_busy == 1) {
        $('#event_form_show_busy').prop('checked', true);
    } else {
        $('#event_form_show_busy').prop('checked', false);
    }
    if (data.repeat_event == 1) {
        $('#event_form_repeat_event').prop('checked', true);
    } else {
        $('#event_form_repeat_event').prop('checked', false);
    }

    $('#event_form_start_time_hour').val(data.start_hour);
    $('#event_form_start_time_minute').val(data.start_minute);

    $('#event_form_end_time_hour').val(data.end_hour);
    $('#event_form_end_time_minute').val(data.end_minute);
    $('#event_form_end_repeat_date').val(data.end_repeat_date);
    $('#event_form input:radio[value=' + data.repeat_type + ']').prop('checked', true);

    switch (data.repeat_type) {
        case 'weekly':
            $(data.repeat_vars).each(function (val) {
                $('#event_form input:checkbox[value=' + val + ']').prop('checked', true);
            });
            break;

        case 'monthly':
            var mselect = data.repeat_vars[0];
            $('#event_form_monthly_repeat').val(mselect);
            break;

        case 'every':
            $('#event_form_every_repeat_number').val(data.repeat_vars[0]);
            $('#event_form_every_repeat_weekday').val(data.repeat_vars[1]);
            $('#event_form_every_repeat_frequency').val(data.repeat_vars[2]);
            break;
    }
}

function resetEventForm()
{
    $('#event-error').html('');
    $('#event-error').hide();

    $('#event_form input[type="text"]').val('');
    $('#event_form input[type="checkbox"]').attr('checked', false);
    $('#event_form input[type="radio"]').prop('checked', false);
    //$('#event_form select option:first').val();
    $('#event_form select').each(function (i, v) {
        first_option = $('option:first', this).val();
        $(this).val(first_option);
    });
}