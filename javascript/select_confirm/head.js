<script type="text/javascript">
//<![CDATA[

function confirmSelect(form, select_id, action_match, message) {

    oSelect = document.getElementById(select_id);

    if (oSelect.value == action_match) {
        if (confirm(message)) {
            form.submit();
        } else {
            return;
        }
    } else {
        form.submit();
    }
}
//]]>
</script>
