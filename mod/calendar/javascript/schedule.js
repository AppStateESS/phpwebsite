resetScheduleForm = function () {
    $('#schedule_form_public_0').prop('checked', false);
    $('#schedule_form_public_1').prop('checked', true);
    $('#schedule_form_title').val('');
    $('#schedule_form_summary').html('');
    $('#schedule_form_show_upcoming').val(0);
    for (instance in CKEDITOR.instances) {
        CKEDITOR.instances[instance].updateElement();
        CKEDITOR.instances[instance].setData('');
    }
};

$(document).ready(function () {
    $('#create-schedule').click(function () {
        resetScheduleForm();
        $('#schedule-modal').modal('show');
    });
});
