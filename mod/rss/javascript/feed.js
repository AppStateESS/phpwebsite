$(window).load(function() {
    $('.edit-feed').click(function() {
        var id = $(this).data('id');
        $.getJSON('index.php', {
            module: 'rss',
            command: 'feedInfo',
            id: id
        }, function(data){
            $('#phpws_form_title').val(data.title);
            $('#phpws_form_address').val(data.address);
            $('#phpws_form_item_limit').val(data.item_limit);
            $('#phpws_form_refresh_time').val(data.refresh_time);
            $('#phpws_form_feed_id').val(id);
        });
        $('#rss-modal').modal('show');
    });
    
    $('#save-feed').click(function(){
        $('#phpws_form').submit();
    });
});