<script type="text/javascript">
//<![CDATA[

function drop(auth_id){
   if (confirm('{DROP_Q}')){
     	location.href =	"index.php?module=users&action=admin&command=dropAuthScript&script_id=" + auth_id;
   }
}

//]]>
</script>
