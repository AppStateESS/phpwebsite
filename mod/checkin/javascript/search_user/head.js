<script type="text/javascript" src="javascript/jquery/jquery.autocomplete.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    $("#send_note_username").autocomplete(
                                          "index.php",
            {
                extraParams:{module:'notes',command:'search_users'},
                delay:10,
                minChars:2,
                matchSubset:1,
                matchContains:1,
                cacheLength:10,
                autoFill:true
            }
                                          );
});


</script>
