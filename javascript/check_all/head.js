<script type="text/javascript">
//<![CDATA[

/**
 * @version $Id$
 * @author Matt McNaney <mcnaney at gmail dot com>
 */
var checked_status = false;

function CheckAll(button, shortcut) {
    var myregexp = new RegExp(shortcut);
    for (i=0; checkbox = button.form.elements[i]; i++) {
        if (myregexp.test(checkbox.name) && checkbox.type=='checkbox') {
            if (checked_status) {
                checkbox.checked = '';
            } else {
                checkbox.checked = 'checked';
            }
        }
    }
    if (checked_status) {
        button.value = '{check_label}';

    } else {
        button.value = '{uncheck_label}';
    }

    checked_status = !checked_status;
}

//]]>
</script>
