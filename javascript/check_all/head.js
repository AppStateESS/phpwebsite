<script type="text/javascript">
//<![CDATA[

var checked = 0;

function CheckAll(button, shortcut) {
    
    for (var i = 0; i < shortcut.length; i++) {
        if (checked == 0) {
            shortcut[i].checked = 'checked';
        } else {
            shortcut[i].checked = '';
        }
    }

    if (checked == 0) {
        button.value = '{uncheck_label}';
        checked = 1;
    } else {
        button.value = '{check_label}';
        checked = 0;
    }

}

//]]>
</script>
