<script type="text/javascript">
$(document).ready(function(){
    $('a.thumb-nav').click(function() {
        sid = $(this).attr('id');
        sort = sid.replace('goto', '');
        address = 'index.php?module=cycle&aop=form&sid=' + sort;
        $.getJSON(address, function(data) {
            var slot_order = data.slot_order;
            
            if (data.new == 0) {
                $('#phpws_form_add_new').val('Update slot ' + slot_order);
            } else {
                $('#phpws_form_add_new').val('Add new slot ' + slot_order);
            }
            
            $('#phpws_form_delete').val('Delete slot ' + slot_order);
            
            $('#slot-no').html('#' + data.slot_order);
            $('#phpws_form_thumbnail_text').val(data.thumbnail_text);
            $('#phpws_form_feature_x').val(data.feature_x);
            $('#phpws_form_feature_y').val(data.feature_y);
            $('#phpws_form_f_width').val(data.f_width);
            $('#phpws_form_f_height').val(data.f_height);
            $('#phpws_form_destination_url').val(data.destination_url);
            $('#phpws_form_slot_order').val(data.slot_order);
            if (data.feature_text != null) {
                CKEDITOR.instances['phpws_form_feature_text'].setData(data.feature_text);
            } else {
                CKEDITOR.instances['phpws_form_feature_text'].setData('<p></p>');
            }
        });
        return false;
    });
});
</script>