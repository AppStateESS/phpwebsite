function check(form_button)
{
    form = form_button.form;
    var form_ok = true;

    $(':input', form).each(function() {
        form_id = $(form).attr('id');

        if ($(this).hasClass('input-required') && $(this).val() == '') {
            var type = this.type;
            $(this).css('border-color', 'red');
            switch (type) {
                case 'text':
                case 'textarea':
                case 'file':
                    element_id = $(this).attr('id');
                    label_id = '#' + element_id + '-label';
                    label_value = $(label_id).html();
                    alert(check_message + label_value);
                    form_ok = false;
                    break;

                default:
                    alert(type);
            }
            return false;
        }
    });

    if (!form_ok) {
        $(form).submit(function() {
            return false;
        });
    } else {
        form.submit();
        return true;
    }
}