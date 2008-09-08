<script type="text/javascript">
//<![CDATA[
     text = opener.document.getElementById('{cnt_section_name}');
     hidden = opener.document.getElementById('{hdn_section_name}');
     content = '{content}';
     if (content == '') {
         content = '&nbsp;';
     }
     text.innerHTML = content;
     hidden.value = '{hidden_value}';
     window.close();
//]]>
</script>
