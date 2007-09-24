<script type="text/javascript">
//<![CDATA[

    var all_checked = 0;

    function users_check_all(level) {
        form = document.getElementById('phpws_form');

        ur_label1 = '{ur_label1}';
        ur_label2 = '{ur_label2}';

        r_label1 = '{r_label1}';
        r_label2 = '{r_label2}';

        for (i=0; i < 500; i++) {
            element = form.elements[i];
            if (!element) {
                continue;
            }
            if (element.type == 'radio') {
                if (all_checked) {
                    if (element.value == 0) {
                        element.checked = true;
                    }
                } else if (element.value == level) {
                    element.checked = true;
                }
            }

            if (element.type == 'checkbox') {
                if (all_checked) {
                    element.checked = false;
                } else {
                    element.checked = true;
                }
            }
        }
        if (all_checked) {
            all_checked = 0;
            if (level == '2') {
                document.getElementById('check-all-restricted').style.display = 'inline';
            } else {
                document.getElementById('check-all-unrestricted').style.display = 'inline';
            }
            document.getElementById('check-all-unrestricted').value = ur_label2;
            document.getElementById('check-all-restricted').value = r_label2;
        } else {
            if (level == '2') {
                document.getElementById('check-all-restricted').style.display = 'none';
            } else {
                document.getElementById('check-all-unrestricted').style.display = 'none';
            }

            all_checked = 1;
            document.getElementById('check-all-unrestricted').value = ur_label1;
            document.getElementById('check-all-restricted').value = r_label1;
        }
    }

//]]>
</script>
