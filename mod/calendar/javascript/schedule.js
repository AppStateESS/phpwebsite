resetScheduleForm = function () {
    $('#schedule_form_public_0').prop('checked', false);
    $('#schedule_form_public_1').prop('checked', true);
    $('#schedule_form_title').val('');
    $('#schedule_form_summary').html('');
    $('#schedule_form_show_upcoming').val(0);
    $('#schedule_form_sch_id').val(0);
    setCKEDITOR('');
};


setCKEDITOR = function (content) {
    CKEDITOR.instances['schedule_form_summary'].updateElement();
    CKEDITOR.instances['schedule_form_summary'].setData(content);
};

plugScheduleForm = function (schedule_id) {
    $.get('index.php?module=calendar&aop=schedule_json&sch_id=' + schedule_id,
            function (data) {
                $('#schedule_form_title').val(data.title);
                $('#schedule_form_summary').html(data.summary);
                var summary = data.summary;
                setCKEDITOR(summary);
                $('#schedule_form_show_upcoming').val(data.show_upcoming);
                $('#schedule_form_sch_id').val(schedule_id);
                console.log(data.public);
                if (data.public == 1) {
                    $('#schedule_form_public_1').prop('checked', true);
                    $('#schedule_form_public_0').prop('checked', false);
                } else {
                    $('#schedule_form_public_1').prop('checked', false);
                    $('#schedule_form_public_0').prop('checked', true);
                }
            }, 'json');
};

getToday = function () {
    var sd = new Date;

    var sd_d = sd.getDate() + 1;

    if (sd_d < 10) {
        sd_d = '0' + sd_d;
    }
    var sd_m = sd.getMonth();
    if (sd_m < 10) {
        sd_m = '0' + sd_m;
    }

    return sd.getFullYear() + '/' + sd_m + '/' + sd_d
}

errorCheckEventForm = function () {
    var error = false;
    var start_date = $('#event_form_start_date').val();
    var end_date = $('#event_form_end_date').val();
    var error_message = new Array();


    if ($('#event_form_summary').val().length == 0) {
        error = true;
        error_message.push('Summary is empty');
    }

    if (!$('#event_form_all_day').is(':checked')) {
        if ($('#event_form_start_time_hour').val() == $('#event_form_end_time_hour').val()) {
            if ($('#event_form_start_time_minute').val() > $('#event_form_end_time_minute').val()) {
                error_message.push('End time is before start time');
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

$(document).ready(function () {
    editor = CKEDITOR.replace('schedule_form_summary',
            {
                on:
                        {
                            instanceReady: function (ev)
                            {
                                this.dataProcessor.writer.indentationChars = '  ';

                                this.dataProcessor.writer.setRules('th',
                                        {
                                            indent: true,
                                            breakBeforeOpen: true,
                                            breakAfterOpen: false,
                                            breakBeforeClose: false,
                                            breakAfterClose: true
                                        });
                                this.dataProcessor.writer.setRules('li',
                                        {
                                            indent: true,
                                            breakBeforeOpen: true,
                                            breakAfterOpen: false,
                                            breakBeforeClose: false,
                                            breakAfterClose: true
                                        });
                                this.dataProcessor.writer.setRules('p',
                                        {
                                            indent: true,
                                            breakBeforeOpen: true,
                                            breakAfterOpen: true,
                                            breakBeforeClose: true,
                                            breakAfterClose: true
                                        });
                            }
                        }
            }
    );

    $('#create-schedule').click(function () {
        resetScheduleForm();
        $('#schedule-modal').modal('show');
    });

    $('#edit-schedule').click(function () {
        var schedule_id = $(this).data('scheduleId');
        resetScheduleForm();
        plugScheduleForm(schedule_id);
        $('#schedule-modal').modal('show');
    });

    $('#add-event').click(function () {
        var schedule_id = $(this).data('schedule-id');
        $('#event-error').html('');
        $('#event-error').hide();

        var start_date = getToday();
        var end_date = getToday();

        $('#event_form input[type="text"]').val('');
        $('#event_form input[type="checkbox"]').attr('checked', false);
        $('#event_form input[type="radio"]').prop('checked', false);
        //$('#event_form select option:first').val();
        $('#event_form select').each(function(i, v) {
            first_option = $('option:first', this).val();
            $(this).val(first_option);
        });

        $('#event-pick').removeClass('inactive');
        $('#event-pick').addClass('active');
        $('#repeat-pick').removeClass('active');
        $('#repeat-pick').addClass('inactive');

        $('#event_form_start_date').val(start_date);
        $('#event_form_end_date').val(end_date);
        $('event_form_sch_id').val(schedule_id);
        $('#edit-event').modal('show');
    });

    $('#submit-event').click(function () {
        if (errorCheckEventForm()) {
            $('#event_form').submit();
        }
    });

});
