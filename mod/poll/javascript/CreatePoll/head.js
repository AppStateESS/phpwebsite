<script language="javascript">
$(document).ready(function() {
    $('.cnp-form').hide();
    $('.cnp-overlay').append('<a href="javascript:createPoll();">Create a new poll for this item.</a>');

/*    $('#create-new-poll').submit(function() {
        createPollAjaxSubmit();
        //return false;
    });*/
});

function createPoll()
{
    $('.cnp-overlay').hide('slow');
    $('.cnp-form').show('slow');
}

function createPollAjaxSubmit()
{
    alert('This is where I would submit.');
}
</script>
