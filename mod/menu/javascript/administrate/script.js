var menu_admin = new MenuAdmin;
// assigned by Menu_Admin::menuList
$(window).load(function() {
    menu_admin.menu_id = z.first_menu_id;
    menu_admin.init();
});


function MenuAdmin() {
    var t = this;
    var current_link;

    var link_id;
    var key_id;
    var menu_id;

    var modal;
    var alert;

    var input;
    var button;

    function Input()
    {
        var inp = this;
        var title;
        var url;
        var key_select;

        this.init = function() {
            this.title = $('#form-title');
            this.url = $('#form-url');
            this.key_select = $('#form-key-select');
            this.locationSwitch();
        };

        this.reset = function() {
            this.title.val('');
            this.url.val('');
            this.select('--');
            $('.form-url-group').show();
            $('.form-key-group').show();
        };

        this.select = function(key_id) {
            $('option:selected', this.key_select).removeAttr('selected');
            $('option[value="' + key_id + '"]', this.key_select).attr('selected', 'selected');
        };

        this.getSelectedKeyId = function() {
            return $('option:selected', this.key_select).val();
        };

        this.getSelectedText = function() {
            return $('option:selected', this.key_select).html();
        };

        this.locationSwitch = function() {
            this.url.focus(function() {
                inp.select('--');
            });

            this.key_select.focus(function() {
                inp.url.val('');
            });
        };

    }

    function Button()
    {
        var sb;
        var db;

        this.init = function() {
            this.sb = $('#form-link-save');
            this.db = $('#form-link-delete');
        };
    }

    this.init = function() {
        this.input = new Input;
        this.input.init();
        this.button = new Button;
        this.button.init();

        this.alert = $('#warning');
        this.alert.hide();

        this.modal = $('#link-edit-modal');

        $('#create-submenu').click(function() {

        });

        this.preventClick();
        this.selectClick();
        this.keyChange();
        this.editLink();
        this.initSort();
        this.addLinkButton();
        this.saveButton();

        this.modal.on('hidden.bs.modal', function(e) {
            t.alert.html('');
            t.alert.hide();
        });
    };

    this.preventClick = function() {
        $('#menu-admin-area .menu-link-href').click(function(e) {
            e.preventDefault();
        });
    };

    this.editButtons = function() {
        var insert = '\n\
<div class="link-options btn-group btn-group-sm">\n\
<button class="link-edit btn btn-default">\n\
<i class="fa fa-edit"></i> Edit</button>\n\
  <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">\n\
    <i class="fa fa-arrow-circle-down"></i> Move under\n\
  </button>\n\
  <ul class="dropdown-menu" role="menu">\n\
  </ul>\n\
</div>';

        $('#menu-admin-area .menu-link').prepend(insert);
        $('#menu-admin-area .dropdown-toggle').click(function() {
            var link_id = $(this).parents('.menu-link').data('id');
            $('#menu-admin-area .dropdown-menu').html('');
            var links = $(this).parents('.menu-link').siblings();
            var select = new Array();
            for (i = 0; i < links.length; i++) {
                var link = $('a.menu-link-href', $(links[i]));
                var newselect = '<li><a href="javascript:void(0)" class="move-under" data-link-id="' +
                        link.data('linkId') + '">' + link.html() + '</a></li>';
                $('#menu-admin-area .dropdown-menu').append(newselect);
            }
            $('.move-under').click(function() {
                var move_link_id = $(this).data('linkId');
                $.get('index.php', {
                    module: 'menu',
                    command: 'move_under',
                    move_from: link_id,
                    move_to: move_link_id
                }, function(data) {
                }).always(function(){
                    t.populateMenuEdit();
                });
            });
        });
    };


    this.editLink = function() {
        this.editButtons();
        $('.link-edit').unbind('click');
        $('.link-edit').click(function() {
            var link = $('a.menu-link-href', $(this).parents('.menu-link').first());
            t.input.title.val(link.html());
            t.link_id = link.data('linkId');
            t.current_link = link;
            t.key_id = link.data('keyId');
            t.url = link.attr('href');
            t.initButtons();
            if (t.key_id > 0) {
                $('.form-url-group').hide();
                $('.form-key-group').hide();
            } else {
                t.input.url.val(t.url);
                t.input.select(t.key_id);
                $('.form-url-group').show();
                $('.form-key-group').show();
            }
            t.modal.modal('show');
        });
    };


    this.keyChange = function() {
        this.input.key_select.change(function() {
            t.key_id = t.input.getSelectedKeyId();
            t.input.title.val(t.input.getSelectedText());
        });
    };

    this.loadKeyId = function() {
        this.key_id = this.input.getSelectedKeyId();
    };

    this.saveButton = function() {
        t.button.sb.unbind('click');
        t.button.sb.click(function() {
            if (t.key_id === undefined || t.key_id < 1) {
                t.loadKeyId();
            }
            if (t.checkForm()) {
                $.post('index.php', {
                    module: 'menu',
                    command: 'post_link',
                    link_id: t.link_id,
                    menu_id: t.menu_id,
                    title: t.input.title.val(),
                    url: t.input.url.val(),
                    key_id: t.key_id
                }, function(data) {
                    //$('body').prepend(data);
                    //console.log(data);
                }).always(function() {
                    t.modal.modal('hide');
                    t.populateMenuEdit();
                });
            }
        });
    };

    this.checkForm = function() {
        if (t.input.title.val().length < 1) {
            t.input.title.attr('placeholder', z.blank_title);
            t.alert.html(z.title_error);
            t.alert.show();
            return false;
        }

        if (t.input.url.val().length < 1 &&
                $('option:selected', t.input.key_select).val() === '--') {
            t.alert.html(z.url_error);
            t.alert.show();
            return false;
        }
        return true;
    };

    this.populateKeySelect = function() {
        $.get('index.php', {
            module: 'menu',
            command: 'key_select'
        }, function(data) {
            t.input.key_select.html(data);
        });
    };

    this.selectClick = function() {
        $('.menu-edit').unbind('click');
        $('.menu-edit').click(function() {
            t.menu_id = $(this).data('menuId');
            t.populateMenuEdit();
        });
    };

    this.addLinkButton = function() {
        $('#add-link').click(function() {
            t.key_id = 0;
            t.link_id = 0;
            t.initButtons();
            t.input.reset();
            t.populateKeySelect();
            t.modal.modal('show');
        });
    };

    this.populateMenuEdit = function() {
        $.get('index.php', {
            module: 'menu',
            command: 'adminlinks',
            menu_id: t.menu_id
        }, function(data) {
            $('#menu-admin-area').html(data);
        }).always(function() {
            t.editLink();
            t.initSort();
            t.preventClick();
        });
    };

    this.initSort = function()
    {
        $('#menu-admin-area .menu-links').sortable({
            helper: 'clone',
            placeholder: 'menu-state-highlight',
            connectWith: '.menu-links',
            update: function(event, ui) {
                t.sortLink(event, ui);
            }
        });
        $('#menu-admin-area .menu-links').disableSelection();
    };

    this.sortLink = function(event, ui) {
        var moved_row = ui.item;
        var moved_row_id = $('a.menu-link-href', moved_row).data('linkId');
        var next_row = ui.item.next('li.menu-link');
        var next_row_id = $('a.menu-link-href', next_row).data('linkId');

        var prev_row = ui.item.prev('li.menu-link');
        var prev_row_id = $('a.menu-link-href', prev_row).data('linkId');
        $.get('index.php', {
            module: 'menu',
            command: 'move_link',
            move_id: moved_row_id,
            next_id: next_row_id,
            prev_id: prev_row_id
        }, function(data) {
            console.log(data);
        }).always(function() {
            t.populateMenuEdit();
        });
    };

    this.deleteButton = function()
    {
        t.button.db.click(function() {
            $(this).html(z.confirm_delete);
            t.button.db.click(function() {
                var link = this;
                $.get('index.php', {
                    module: 'menu',
                    command: 'delete_link',
                    link_id: t.link_id
                }, function(data) {
                    //console.log(data);
                }).always(function() {
                    t.modal.modal('hide');
                    t.populateMenuEdit();
                });
            });
        });
    };

    this.initButtons = function() {
        if (!t.link_id) {
            t.button.db.hide();
        } else {
            t.button.db.show();
        }
        t.button.db.unbind('click');
        t.button.db.html(z.delete);
        t.deleteButton();
    };
}