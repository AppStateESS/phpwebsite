<script type="text/javascript">
//<![CDATA[

window.onload = function() {
    parent_input = opener.document.getElementById('{parent_section}');
    new_form = document.getElementById('{edit_input}');
    new_form.value = html_entity_decode(parent_input.value);
}

/**
 * From: http://javascript.internet.com/
 * By Ultimater
 */ 
function html_entity_decode(str) {
    var ta=document.createElement("textarea");
    ta.innerHTML=str.replace(/</g,"&lt;").replace(/>/g,"&gt;");
    return ta.value;
}
//]]>
</script>
