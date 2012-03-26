<script type="text/javascript">
//<![CDATA[
id_name = '#{cnt_section_name}';
$(document).ready(function() {
    content = '{content}';
     if (content == '') {
         content = '&nbsp;';
     }
    jopen = window.opener.jQuery(id_name);
    jopen.html(content);
    opener.disable_links();
    opener.mark_changed();
    window.close();
});
//]]>
</script>
