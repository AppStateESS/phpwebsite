<script type=\"text/javascript\">
//<![CDATA[

function change_title(){
 var new_title = "";

 new_title = prompt('{QUESTION}', '{TITLE}');

 new_title = new_title.replace(/[^{ALLOWED}]+/g, "");
 new_title = new_title.replace(/\s+/g, "+");

// alert(new_title);

 location.href = "index.php?module=related&action=postTitle&new_title=" + new_title;
 
}

//]]>
</script>
