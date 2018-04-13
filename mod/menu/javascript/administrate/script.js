var menu_admin = new MenuAdmin;
// assigned by Menu_Admin::menuList
$(window).load(function() {
    menu_admin.menu_id = translate.first_menu_id;
    menu_admin.selected_menu_id = menu_admin.menu_id;
    menu_admin.init();
    //$('#form-key-select').select2();
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
    var settings;

    function Input()
    {
        var inp = this;
        var title;
        var url;
        var key_select;
        var assoc_page;
        var assoc_url;

        this.init = function() {
            this.title = $('#form-title');
            this.url = $('#form-url');
            this.assoc_url = $('#menu-associated-url');
            this.key_select = $('#form-key-select');
            this.assoc_page = $('#menu-associated-page');
            this.assoc_image_thumbnail = $('#assoc-image-thumbnail');
            this.locationSwitch();
            this.associatedPage();
            this.associatedUrl();
        };

        this.reset = function() {
            this.title.val('');
            this.url.val('');
            this.select('0');
            $('.form-url-group').show();
            $('.form-key-group').show();
        };

        this.associatedPage = function() {
            this.assoc_page.change(function() {
                t.input.assoc_url.val('');
            });
        };

        this.setAssocPage = function(assoc_key) {
            $('#menu-associated-page option').removeAttr('selected');
            $('#menu-associated-page option[value="' + assoc_key + '"]').attr('selected', 'selected');
        };

        this.setAssocUrl = function(assoc_url)
        {
            this.assoc_url.val(assoc_url);
        };

        this.setAssocImageThumbnail = function(assoc_image_thumbnail) {
            var tn = '<img src="' + assoc_image_thumbnail + '" style="max-width:200px" />';
            this.assoc_image_thumbnail.html(tn);
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

        this.associatedUrl = function() {
            this.assoc_url.focus(function() {
                t.input.setAssocPage(0);
            });
        };

    }

    /**
     *
     * @returns {MenuAdmin.Settings}
     */
    function Settings()
    {
        this.init = function() {
            $('#menu-link-limit').change(function() {
                var link_limit = $('option:selected', this).val();
                $.get('index.php', {
                    module: 'menu',
                    command: 'update_character_limit',
                    limit: link_limit
                }).always(function() {
                    $('#settingsModal').modal('hide');
                });
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
            this.pinned_button = $('#pinned-button');

            this.addLinkButton();
            this.editMenuButton();
            this.deleteMenuButton();
            this.saveMenuButton();
            this.pinnedMenuButton();
            this.createMenu();
            this.clearImage();
        };


        this.clearImage = function() {
            $('#clear-image').click(function() {
                $.get('index.php', {
                    module: 'menu',
                    command: 'clear_image',
                    menu_id: t.menu_id
                }).always(function() {
                    $('#assoc-image-thumbnail').remove();
                    $('#clear-image').hide();
                });
            });
        };

        this.createMenu = function() {
            $('#create-menu').click(function() {
                t.resetMenuForm();
                t.menu_modal.modal('show');
            });
        };


        this.addLinkButton = function() {
            if (t.menu_id === undefined || t.menu_id < 1) {
                $('#add-link').hide();
            } else {
                $('#add-link').show();
                $('#add-link').click(function() {
                    //t.resetAssociatedPage();
                    t.key_id = 0;
                    t.link_id = 0;
                    t.initFormButtons();
                    t.input.reset();
                    t.link_modal.modal('show');
                });
            }
        };

        this.deleteButton = function() {
            this.db.click(function() {
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
                        t.populateKeySelect();
                    });
                });
            });
        };

        this.editMenuButton = function() {
            if (t.menu_id === undefined || t.menu_id < 1) {
                $('#edit-menu').hide();
            } else {
                $('#edit-menu').show();
                this.initializeCarousel();
                $('#edit-menu').click(function() {
                    t.resetMenuForm();
                    t.menu_id = t.selected_menu_id;
                    $.get('index.php', {
                        module: 'menu',
                        command: 'menu_data',
                        menu_id: t.menu_id
                    }, function(data) {
                        $('#menu-title').val(data.title);
                        if (data.assoc_image_thumbnail.length > 0) {
                            $('#clear-image').show();
                        } else {
                            $('#clear-image').hide();
                        }

                        $('#menu-template option').removeAttr('selected');
                        $('#menu-template option[value="' + data.template + '"]')[0].selected = true;
                        t.input.setAssocPage(data.assoc_key);
                        t.input.setAssocImageThumbnail(data.assoc_image_thumbnail);
                        if (data.assoc_image_thumbnail.length > 0) {
                            $('#clear-image').show();
                        }
                        if (data.assoc_key === '0') {
                            t.input.setAssocUrl(data.assoc_url);
                        }
                    }, 'json');
                    t.menu_modal.modal('show');
                });
            }
        };

        this.initializeCarousel = function() {
            var caro = $('#carousel-slide');
            if (caro.text().length < 1) {
                return;
            } else {
                caro.change(function() {
                    var slide = $('option:selected', this).val();
                    t.input.setAssocImageThumbnail(slide);
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

        this.saveMenuButton = function() {
            $('#form-menu-save').click(function() {
                var title = $('#menu-title').val();
                var akey = $('#menu-associated-page option:selected').val();
                if (akey === undefined) {
                    akey = 0;
                }
                if (title.length < 1) {
                    $('#menu-title').attr('placeholder', translate.title_error);
                } else {
                    $('#menu-id').val(t.menu_id);
                    $('#menu-edit-form').submit();
                }
            });
        };

        this.saveButton = function() {
            this.sb.unbind('click');
            this.sb.click(function() {
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
                        //console.log(data);
                    }).always(function() {
                        t.link_modal.modal('hide');
                        t.populateMenuEdit();
                        t.populateKeySelect();
                    });
                }
            });
        };

        this.pinnedMenuButton = function()
        {
            var change_pin_all = 0;
            this.pinned_button.click(function() {
                if (t.pin_all) {
                    change_pin_all = 0;
                } else {
                    change_pin_all = 1;
                }
                $.get('index.php', {
                    module: 'menu',
                    command: 'pin_all',
                    menu_id: t.menu_id,
                    pin_all: change_pin_all
                }, function(data) {
                    //console.log(data);
                }).always(function() {
                    t.pin_all = change_pin_all;
                    t.populateMenuEdit();
                });
            });
        };

    }

    this.init = function() {
        this.input = new Input;
        this.input.init();
        this.button = new Button;
        this.button.init();
        this.settings = new Settings;
        this.settings.init();
        this.pin_all = fmp;


        this.alert = $('.warning');
        this.alert.hide();

        this.link_modal = $('#link-edit-modal');
        this.menu_modal = $('#menu-modal');

        this.selectClick();
        this.resetLinks();

        this.keyChange();
        this.displayType();
        this.menuCheckbox();
        this.forceShortcuts();

        this.populateKeySelect();

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

    this.resetMenuForm = function() {
        t.menu_id = 0;
        $('#menu-title').val('');
        $('#menu-template option:selected').removeAttr('selected');
        $('#menu-associated-image').val('');
        $('#clear-image').hide();
        t.input.setAssocPage(0);
        t.input.setAssocImageThumbnail('');
        t.input.setAssocUrl('');
    };

    this.resetAssociatedPage = function() {
        //$('#menu-associated-page').select2('data', {id:null,text:null});
    };


    this.menuCheckbox = function() {
        $('#home-link').change(function() {
            $.get('index.php', {
                module: 'menu',
                command: 'new_link_menu',
                check: $(this).prop('checked')
            });
        });
        $('#link-icons').change(function() {
            $.get('index.php', {
                module: 'menu',
                command: 'link_icons',
                check: $(this).prop('checked')
            });
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
            }).always(function() {
                window.location.reload();
            });
        });
    };

    this.forceShortcuts = function() {
        $('#force-shortcuts').click(function() {
            $.get('index.php', {
                module: 'menu',
                command: 'force_shortcut'
            }).always(function() {
                window.location.reload();
            });
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
                var newselect = '<li><a href="javascript:void(0)" class="dropdown-item move-under " data-link-id="' +
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
            var link = $($('a.menu-link-href', $(this).parents('.menu-link').first())[0]);
            t.input.title.val(link.text());
            t.link_id = link.data('linkId');
            t.current_link = link;
            t.key_id = link.data('keyId');
            t.url = link.attr('href');
            t.loadMoveLinkOptions();
            t.initFormButtons();
            console.log(t.url);
            $('.current-association a').text(t.url);
            $('.current-association a').attr('href', t.url);
            if (t.key_id > 0) {
                $('.form-url-group').hide();
                $('.current-association').show();
                $('.form-key-group').hide();
            } else {
                t.input.url.val(t.url);
                $('.current-association').hide();
                t.input.select(t.key_id);
                $('.form-url-group').show();
                $('.form-key-group').hide();
            }
            t.link_modal.modal('show');
        });
    };

    this.loadMoveLinkOptions = function() {
        $.get('index.php', {
            module: 'menu',
            command: 'menu_options',
            menu_id: t.menu_id
        }, function(data) {
            $('#move-to-menu').html(data);
        });

        $('#move-to-menu').change(function() {
            var menu_id = $('option:selected', this).val();
            $.get('index.php', {
                module: 'menu',
                command: 'transfer_link',
                menu_id: menu_id,
                link_id: t.link_id
            }).always(function() {
                t.link_modal.modal('hide');
                t.populateMenuEdit();
            });
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
        $('#menu-associated-page option').removeAttr('selected');
        $.get('index.php', {
            module: 'menu',
            command: 'key_select'
        }, function(data) {
            t.input.key_select.html(data);
            $('#menu-associated-page').html(data);
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

    this.populateMenuEdit = function() {
        $.get('index.php', {
            module: 'menu',
            command: 'adminlinks',
            menu_id: t.menu_id
        }, function(data) {
            $('#menu-admin-area').html(data.html);
            if (data.pin_all == '1') {
                t.pin_all = 1;
                t.button.pinned_button.html(translate.pin_all);
                t.button.pinned_button.removeClass('btn-default');
                t.button.pinned_button.addClass('btn-primary');
            } else {
                t.pin_all = 0;
                t.button.pinned_button.removeClass('btn-primary');
                t.button.pinned_button.addClass('btn-default');
                t.button.pinned_button.html(translate.pin_some);
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
        var waiting = '<div style="z-index: 100;text-align:center;position:absolute;  height:100%; width:100%">\n\
<div style="margin: 0px auto; width : 200px; height : 200px; padding-top : 80px; opacity:.90; background-color: #e3e3e3; text-align:center;">\n\
<img src="mod/menu/img/waiting.gif" /> Working... \n\
</div>\n\
</div>';
        $('#menu-admin-area').prepend(waiting);
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


    this.initFormButtons = function() {
        if (!t.link_id) {
            $('#move-to-menu').hide();
            t.button.db.hide();
        } else {
            $('#move-to-menu').show();
            t.button.db.show();
        }
        t.button.db.unbind('click');
        t.button.db.html(translate.delete);
        t.button.saveButton();
        t.button.deleteButton();
    };
}