$(window).load(function(){
    $('#menu-add-page').change(function(){
        key_id = $(this).data('keyId');
        menu_id = $(this).val();
        $.get('index.php', {
            module:'menu',
            command: 'add_key_link',
            key_id: key_id,
            menu_id: menu_id
        }).always(function(){
            window.location.reload();
        });
    });

    $('#menu-add-page').click(function(e){
        e.stopPropagation();
    });

    $('#menu-pin-page').change(function(){
        key_id = $(this).data('keyId');
        menu_id = $(this).val();
        $.get('index.php', {
            module:'menu',
            command: 'pin_menu',
            key_id: key_id,
            menu_id: menu_id
        }).always(function(){
            window.location.reload();
        });
    });
    $('#menu-pin-page').click(function(e){
        e.stopPropagation();
    });

    $('#menu-unpin-page').change(function(){
        key_id = $(this).data('keyId');
        menu_id = $(this).val();
        $.get('index.php', {
            module:'menu',
            command: 'unpin_menu',
            key_id: key_id,
            menu_id: menu_id
        }, function(data){
            //console.log(data);
        }).always(function(){
            window.location.reload();
        });
    });
    $('#menu-unpin-page').click(function(e){
        e.stopPropagation();
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