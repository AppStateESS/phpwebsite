$(window).load(function(){
    $('#menu-add-page').change(function(){
        key_id = $(this).data('keyId');
        menu_id = $(this).val();
        console.log('key id=' + key_id + ', menu id=' + menu_id);
        $.get('index.php', {
            module:'menu',
            command: 'add_key_link',
            key_id: key_id,
            menu_id: menu_id
        }).always(function(){
            window.location.reload();
        });
    });
    $('#menu-remove-page').click(function(){
        key_id = $(this).data('keyId');
        menu_id = $(this).data('menuId');
        $.get('index.php', {
            module:'menu',
            command: 'remove_key_link',
            key_id: key_id,
            menu_id: menu_id
        }).always(function(){
            window.location.reload();
        });
    });
});