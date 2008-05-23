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

edit_link = opener.document.getElementById(link_id);
onclick_val = edit_link.innerHTML;
edit_link.innerHTML = onclick_val.replace(/fid=\d+&/, 'fid=' + new_id + '&')

hidden = opener.document.getElementById(hidden_id);
hidden.value = new_id;

window.close();

//]]>
</script>
