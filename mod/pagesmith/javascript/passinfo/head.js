<script type="text/javascript">
//<![CDATA[

    var url  = '{url}';

window.onload = function() {
    parent_input = opener.document.getElementById('{parent_section}');
    new_form = document.getElementById('{edit_input}');
    new_data = html_entity_decode(parent_input.value);
    new_data = new_data.replace(/src="(\.\/|)images/gi, 'src="' + url + 'images');
    new_form.value = new_data;
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
