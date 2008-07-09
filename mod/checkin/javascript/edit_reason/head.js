<script type="text/javascript">
//<![CDATA[


function show_prompt() {
    var answer = '';
    var address = '{address}';

    reason_id = $('#reasons_edit_reason').val();
    reason    = $('#reasons_edit_reason option').html();

    var prompt_result = prompt('{question}', reason);

    if (prompt_result == null || prompt_result == "") {
	return;
    }

    address = address + '&reason=' + encode(prompt_result) + '&reason_id=' + reason_id;
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