<script type="text/javascript">
//<![CDATA[

function CheckAll() {
   for (var i = 0; i < document.{FORM_NAME}.elements.length; i++) {
       if( document.{FORM_NAME}.elements[i].type == 'checkbox' ) {
           document.{FORM_NAME}.elements[i].checked = !(document.{FORM_NAME}.elements[i].checked);
       }
   }
}

//]]>
</script>
