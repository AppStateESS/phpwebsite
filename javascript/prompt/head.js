<script type="text/javascript">
//<![CDATA[
function show_prompt(question, address, answer, prompt_var) {
    var prompt_result = prompt(question, answer);

    if (prompt_result == null || prompt_result == "") {
	return;
    }

    address = address + '&' + prompt_var + '=' + encode(prompt_result);
    location.href = address;
}

function encode(str) {
	var result = "";
	
	for (i = 0; i < str.length; i++) {
		if (str.charAt(i) == " ") result += "+";
		else result += str.charAt(i);
	}
        return result;
}

//]]>
</script>

