<script type="text/javascript">
//<![CDATA[

var checked = new Array();

function CheckAll(button, shortcut) {
    if(undefined == checked[shortcut.name]) {
        checked[shortcut.name] = 0;
    }

    for (var i = 0; i < shortcut.length; i++) {
        if (checked[shortcut.name] == 0) {
            shortcut[i].checked = 'checked';
        } else {
            shortcut[i].checked = '';
        }
    }

    if (checked[shortcut.name] == 0) {
        button.value = '{uncheck_label}';
        checked[shortcut.name] = 1;
    } else {
        button.value = '{check_label}';
        checked[shortcut.name] = 0;
    }

}

//]]>
</script>
