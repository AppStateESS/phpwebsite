<script type="text/javascript">

function check(form_button)
{
    form = form_button.form;
    var form_ok = true;

    $(':input', form).each(function() {
        var class = $(this).attr('class');
        if (class == 'input-required') {
            var type = this.type;
            switch(type) {
            case 'text':
                if ($(this).val() == '') {
                    element_id = $(this).attr('id');
                    label_id = '#' + element_id + '-label';
                    label_value = $(label_id).html();
                    alert('Please fill out the field: ' + label_value);
                    form_ok = false;
                    return false;
                }
            }
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
</script>

    
