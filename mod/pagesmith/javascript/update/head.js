<script type="text/javascript">
//<![CDATA[
     text = opener.document.getElementById('{cnt_section_name}');
     content = '{content}';
     if (content == '') {
         content = '&nbsp;';
     }
     text.innerHTML = content;
     opener.disable_links();
     opener.mark_changed();
     window.close();
//]]>
</script>
