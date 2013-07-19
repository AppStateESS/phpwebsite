$(window).load(function() {
    fbs = new formatBootstrap;
    fbs.checkWidth();
    fbs.toggleSubmenu();
    $(window).resize(function() {
        fbs.toggleSubmenu();
        fbs.checkWidth();
    });
});

function formatBootstrap() {
    this.window_width = $(window).width();
    this.subnav_up = false;

    this.checkWidth = function() {
        this.window_width = $(window).width();
        var nav_height = $('div.menu ul.nav').height();

        if (this.window_width < 768) {
            if ($('ul.nav > li.menu-link > div').length > 0 && !this.subnav_up) {
                this.subnav_up = true;
                var submenu = $('.menu .nav').height();
                $('ul.nav > li.menu-link > div').animate({right: '+=1500', 'duration': 'slow', 'easing': 'linear'});
                $('ul.nav > li.menu-link > div').css('minHeight', submenu);
                $('ul.nav > li.menu-link > div').prepend('<div id="menu-chevron"><i class="icon-chevron-left icon-white"></i></div><div class="submenu-label">Submenu of ' + $('li.current-link a').html() + '</div>' );
                $('#menu-chevron').css('height', $('ul.nav > li.menu-link > div').height());
            }
        } else {
            if (this.subnav_up) {
                $('ul.nav > li.menu-link > div').animate({right: '-=1500'});
                $('ul.nav > li.menu-link > div').css('minHeight', '');
            }
            $('#menu-chevron').remove();
            this.subnav_up = false;
        }
    }

    this.toggleSubmenu = function() {
        _ = this;
        $('#menu-chevron').click(function() {
            if (_.subnav_up) {
                $('ul.nav > li.menu-link > div').animate({right: '-=96%'});
                _.subnav_up = false;
            } else {
                $('ul.nav > li.menu-link > div').animate({right: '+=96%'});
                _.subnav_up = true;
            }
        });
    }
}




