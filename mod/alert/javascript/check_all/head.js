<script type="text/javascript">
//<![CDATA[

/**
 * @version $Id$
 * @author Matt McNaney <mcnaney at gmail dot com>
 */
var checked_status = false;

function AlertCheckAll(link, type_id) {
    form = document.getElementById('participants-form');
    name_o_checkbox = 'type_id[' + type_id + ']';
    for (i=0; checkbox = form.elements[i]; i++) {
        if (checkbox.type=='checkbox' && checkbox.name==name_o_checkbox) {
            if (checked_status) {
                checkbox.checked = '';
            } else {
                checkbox.checked = 'checked';
            }
        }
    }

    if (checked_status) {
        link.innerHTML = '+';
    } else {
        link.innerHTML = '-';
    }

    checked_status = !checked_status;
    return false;
}

//]]>
</script>
