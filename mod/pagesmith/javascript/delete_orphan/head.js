<script type="text/javascript">
//<![CDATA[

function delete_orphan(id)
{
    $.get('index.php', {module:'pagesmith', aop : 'delete_section', sec_id : id}, function(data) {
         $('#'+id).remove()
    });
    return false;
}
//]]>
</script>
