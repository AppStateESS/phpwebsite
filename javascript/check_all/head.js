<script type="text/javascript">
//<![CDATA[

/**
 * @version $Id$
 * @author Matt McNaney <mcnaney at gmail dot com>
 */
var checked_status = false;

function CheckAll(input, shortcut) {
    for (i=0; checkbox = input.form.elements[i]; i++) {
        if (checkbox.type=='checkbox' && checkbox.name.match(shortcut)) {
            if (checked_status) {
                checkbox.checked = '';
            } else {
                checkbox.checked = 'checked';
            }
        }
    }

    if (input.type == 'button') {
        if (checked_status) {
            input.value = '{check_label}';
        } else {
            input.value = '{uncheck_label}';
        }
    }

    checked_status = !checked_status;
}

//]]>
</script>
