<script type="text/javascript">
//<![CDATA[
function show_prompt(question, address, answer) {
    var prompt_result = prompt(question, answer);
    var error_message = '{error_message}';

    if (prompt_result == null || prompt_result == "") {
	alert(error_message);
	return;
    }

    address = address + '&{value_name}=' + encode(prompt_result);
    location.href = address;
}

function encode(str) {
	var result = "";
	
	for (i = 0; i < str.length; i++) {
		if (str.charAt(i) == " ") result += "+";
		else result += str.charAt(i);
	}
	
	return escape(result);
}

//]]>
</script>

