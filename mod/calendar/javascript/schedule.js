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
                if (data.public == 1) {
                    $('#schedule_form_public_1').prop('checked', true);
                    $('#schedule_form_public_0').prop('checked', false);
                } else {
                    $('#schedule_form_public_1').prop('checked', false);
                    $('#schedule_form_public_0').prop('checked', true);
                }
            }, 'json');
};

$(document).ready(function () {
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
});
