<script type="text/javascript">
//<![CDATA[
function show_prompt(question, address, answer) {
    var result = prompt(question, answer);
    var error_message = '{error_message}';

    if (result == null || result == "") {
	alert(error_message);
	return;
    }

    address = address + '&{value_name}=' + result;
    location.href = address;
}
//]]>
</script>

