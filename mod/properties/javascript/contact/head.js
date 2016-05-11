    <script type="text/javascript">
    $(document).ready(function() {
    $('.message').click(function() {
        upload_form($(this));
    });
    $('.report').click(function() {
        report_form($(this));
    });
});

function upload_form(link)
{
    id = link.attr('id');
    $.get('index.php?module=properties&rop=contact', { id: id }, function (data) {
        $('#contact-form').html(data);
    });

    $('#contact-form').dialog({
        autoOpen: false ,
        modal : true,
        title : 'Contact renter',
        width : 500,
        height : 400});

    $('#contact-form').dialog('open');
}

function report_form(link)
{
    id = link.attr('id');
    $.get('index.php?module=properties&rop=report', { id: id }, function (data) {
        $('#report-form').html(data);
    });
    
    $('#report-form').dialog({
        autoOpen: false ,
        modal : true,
        title : 'Report message',
        width : 500,
        height : 300});

    $('#report-form').dialog('open');
    
}
</script>