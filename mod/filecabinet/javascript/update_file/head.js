<script type="text/javascript">
//<![CDATA[

var placeholder = 'pl_{id}';
var hidden_id   = 'h_{id}';
var link_id     = 'l_{id}';
var data        = '{data}';
var new_id      = '{new_id}';

section = opener.document.getElementById(placeholder);

data = data.replace(/&lt;/g, '<');
data = data.replace(/&gt;/g, '>');
data = data.replace(/&quot;/g, '"');
data = data.replace(/&#039;/g, "'");
data = data.replace(/&amp;/g, '&');
section.innerHTML = data;

$('#' + link_id + ' > a').bind("click", function() {
    open_window("{url}", 800, 600, 'edit_file', 1);
});

hidden = opener.document.getElementById(hidden_id);
hidden.value = new_id;

window.opener.carousel('caro-1', {vert}, {vis}, {total_size});
window.close();

//]]>
</script>
