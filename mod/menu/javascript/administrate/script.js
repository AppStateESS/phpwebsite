var menu_admin = new MenuAdmin;
// assigned by Menu_Admin::menuList
$(window).load(function() {
    menu_admin.menu_id = translate.first_menu_id;
    menu_admin.selected_menu_id = menu_admin.menu_id;
    menu_admin.init();
    $('#form-key-select').select2();
});


function MenuAdmin() {
    var t = this;
    var current_link;

    var link_id;
    var key_id;
    var menu_id;
    var selected_menu_id;

    var link_modal;
    var menu_modal;
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
            this.select('0');
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
                inp.select('0');
            });

            this.key_select.focus(function() {
                inp.url.val('');
            });
        };

    }

    /**
     * Class for the modal form buttons save and delete
     * @returns {MenuAdmin.Button}
     */
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
        this.pinned_button = $('#pinned-button');

        this.alert = $('.warning');
        this.alert.hide();

        this.link_modal = $('#link-edit-modal');
        this.menu_modal = $('#menu-modal');

        this.createMenu();
        this.selectClick();
        this.resetLinks();

        this.keyChange();
        this.addLinkButton();
        this.editMenuButton();
        this.deleteMenuButton();
        this.saveMenuButton();
        this.displayType();

        this.link_modal.on('hidden.bs.modal', function(e) {
            t.alert.html('');
            t.alert.hide();
        });
        this.menu_modal.on('hidden.bs.modal', function(e) {
            t.alert.html('');
            t.alert.hide();
            t.menu_id = t.selected_menu_id;
        });
    };

    this.displayType = function() {
        $('#menu-display').change(function() {
            var display_type = $('option:selected', this).val();
            $.get('index.php', {
                module: 'menu',
                command: 'change_display_type',
                display_type: display_type
            }, function(data) {
                //console.log(data);
            }).always(function() {
                window.location.reload();
            });
        });
    };

    this.createMenu = function() {
        $('#create-menu').click(function() {
            t.menu_id = 0;
            $('#menu-title').val('');
            $('#menu-template option:selected').removeAttr('selected');
            t.menu_modal.modal('show');
        });
    };

    this.resetLinks = function() {
        this.preventClick();
        this.editLink();
        this.initSort();
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
<i class="fa fa-edit"></i> ' + translate.edit + '</button>\n\
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
                }).always(function() {
                    t.populateMenuEdit();
                });
            });
        });
    };


    this.editLink = function() {
        this.editButtons();
        $('.link-edit').unbind('click');
        $('.link-edit').click(function() {
            t.populateKeySelect();
            var link = $('a.menu-link-href', $(this).parents('.menu-link').first());
            t.input.title.val(link.html());
            t.link_id = link.data('linkId');
            t.current_link = link;
            t.key_id = link.data('keyId');
            t.url = link.attr('href');
            t.initFormButtons();
            if (t.key_id > 0) {
                $('.form-url-group').hide();
                $('.form-key-group').hide();
            } else {
                t.input.url.val(t.url);
                t.input.select(t.key_id);
                $('.form-url-group').show();
                $('.form-key-group').hide();
            }
            t.link_modal.modal('show');
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

    this.saveMenuButton = function() {
        $('#form-menu-save').click(function() {
            var title = $('#menu-title').val();
            if (title.length < 1) {
                $('#menu-title').attr('placeholder', translate.title_error);
            } else {
                $.post('index.php', {
                    module: 'menu',
                    command: 'post_menu',
                    menu_id: t.menu_id,
                    title: $('#menu-title').val(),
                    template: $('#menu-template option:selected').val()
                }, function(data) {
                    //console.log(data);
                }).always(function() {
                    window.location.reload();
                });
            }
        });
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
                    t.link_modal.modal('hide');
                    t.populateMenuEdit();
                });
            }
        });
    };

    this.checkForm = function() {
        if (t.input.title.val().length < 1) {
            t.input.title.attr('placeholder', translate.blank_title);
            t.alert.html(translate.title_error);
            t.alert.show();
            return false;
        }
        if (t.key_id === '0' && t.input.url.val().length < 1 &&
                $('option:selected', t.input.key_select).val() === '0') {
            t.alert.html(translate.url_error);
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
            t.selected_menu_id = t.menu_id;
            $('#menu-select ul li a').removeClass('active');
            $(this).addClass('active');
            t.populateMenuEdit();
        });
    };

    this.addLinkButton = function() {
        if (t.menu_id === undefined || t.menu_id < 1) {
            $('#add-link').hide();
        } else {
            $('#add-link').show();
            $('#add-link').click(function() {
                t.key_id = 0;
                t.link_id = 0;
                t.initFormButtons();
                t.input.reset();
                t.populateKeySelect();
                t.link_modal.modal('show');
            });
        }
    };

    this.editMenuButton = function() {
        if (t.menu_id === undefined || t.menu_id < 1) {
            $('#edit-menu').hide();
        } else {
            $('#edit-menu').show();
            $('#edit-menu').click(function() {
                t.menu_id = t.selected_menu_id;
                $.get('index.php', {
                    module: 'menu',
                    command: 'menu_data',
                    menu_id: t.menu_id
                }, function(data) {
                    $('#menu-title').val(data.title);
                    $('#menu-template>option:selected').removeAttr('selected');
                    $('#menu-template>option[value="' + data.template + '"]').attr('selected');
                }, 'json');
                t.menu_modal.modal('show');
            });
        }
    };

    this.deleteMenuButton = function() {
        if (t.menu_id === undefined || t.menu_id < 1) {
            $('#delete-menu').hide();
        } else {
            $('#delete-menu').show();
            $('#delete-menu').click(function() {
                if (window.confirm(translate.delete_menu_message)) {
                    $.get('index.php', {
                        module: 'menu',
                        command: 'delete_menu',
                        menu_id: t.menu_id
                    }).always(function() {
                        window.location.reload();
                    });
                }
            });
        }
    };

    this.populateMenuEdit = function() {
        $.get('index.php', {
            module: 'menu',
            command: 'adminlinks',
            menu_id: t.menu_id
        }, function(data) {
            $('#menu-admin-area').html(data.html);
            if (data.pin_all == '1') {
                t.pinned_button.html(translate.pin_all);
                t.pinned_button.removeClass('btn-default');
                t.pinned_button.addClass('btn-primary');
            } else {
                t.pinned_button.removeClass('btn-primary');
                t.pinned_button.addClass('btn-default');
                t.pinned_button.html(translate.pin_some);
            }
        }, 'json').always(function() {
            t.resetLinks();
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

        $('#menu-select ul').sortable({
            helper: 'clone',
            update: function(event, ui) {
                t.sortMenu(event, ui);
            }});
        $('#menu-select ul').disableSelection();
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
            //console.log(data);
        }).always(function() {
            t.populateMenuEdit();
        });
    };

    this.populateMenuSelect = function() {
        $.get('index.php', {
            module: 'menu',
            command: 'populate_menu_select'
        }, function(data) {
            $('#menu-select').html(data);
        });
    };

    this.sortMenu = function(event, ui) {
        var moved_row = ui.item;
        var moved_row_id = $(moved_row).data('menuId');

        var next_row = moved_row.next('li');
        var next_row_id = $(next_row).data('menuId');

        var prev_row = moved_row.prev('li');
        var prev_row_id = $(prev_row).data('menuId');

        $.get('index.php', {
            module: 'menu',
            command: 'move_menu',
            move_id: moved_row_id,
            next_id: next_row_id,
            prev_id: prev_row_id
        });
    };


    this.deleteButton = function()
    {
        t.button.db.click(function() {
            $(this).html(translate.confirm_delete);
            t.button.db.click(function() {
                var link = this;
                $.get('index.php', {
                    module: 'menu',
                    command: 'delete_link',
                    link_id: t.link_id
                }, function(data) {
                    //console.log(data);
                }).always(function() {
                    t.link_modal.modal('hide');
                    t.populateMenuEdit();
                });
            });
        });
    };

    this.initFormButtons = function() {
        if (!t.link_id) {
            t.button.db.hide();
        } else {
            t.button.db.show();
        }
        t.button.db.unbind('click');
        t.button.db.html(translate.delete);
        t.saveButton();
        t.deleteButton();
    };
}