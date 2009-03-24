<script type="text/javascript">

$(document).ready(function() {
    disable_links();
});

function disable_links()
{
    $('.pagesmith-page a:not(#edit-file, #clear-file, .change-link)').click(function() {
        if (confirm('{disable_message}')) {
            location.href = $(this).attr('href');
        }
        return false;
    });
}

</script>
