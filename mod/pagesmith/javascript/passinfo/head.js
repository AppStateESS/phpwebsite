<script type="text/javascript">
//<![CDATA[

var url  = '{url}';

$(document).ready(function() {
    parent_input = $('#{parent_section}', opener.document);
    new_form = $('#{edit_input}');
    new_data = html_entity_decode($(parent_input).val());
    new_data = new_data.replace(/src="(\.\/|)images/gi, 'src="' + url + 'images');
    new_form.val(new_data);
});

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
