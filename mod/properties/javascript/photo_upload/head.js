<script type="text/javascript">
var form_url = '{form_url}';
var property_id = '{pid}';
$(document).ready(function() {
    if (property_id > '0') {
        upload_form();
    }

    $('.photo-upload').click(function() {
        property_id = this.id;
        upload_form();
    });
});

function upload_form()
{
    $.get(form_url, { pid: property_id }, function (data){
        $('#photo-form').html(data);
    });
    $('#photo-form').dialog({
        autoOpen: false ,
        modal : true,
        title : 'Photos',
        width : 600,
        height : 500,
        close : reset_property});

    $('#photo-form').dialog('open');
}

function reset_property()
{
    // if the page just returned from an upload, we refresh the page without the pid
    result = window.location.href.search(/pid/i);
    if (result > 0) {
        new_url = window.location.href.replace(/&pid=\d+/, '');
        window.location.href=new_url;
    } else {
        $('#photo-form').html('');
        property_id = 0;
    }
}

</script>