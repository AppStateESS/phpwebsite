jQuery.fn.outerHTML = function(s) {
    return (s)
            ? this.before(s).remove()
            : jQuery("<p>").append(this.eq(0).clone()).html();
};

$(window).load(function() {
    Pagers = new PagerList;
    Pagers.loadPagers();
    Pagers.fillRows();

    $('.sort-header').click(function() {
        var column_name = $(this).attr('data-column-name');
        var direction = $(this).attr('data-direction');
        var pager_id = $(this).parents('.pager').attr('id');
        var current_icon = $('i', this);
        $('.sort-header i').attr('class', 'icon-stop');
        $('.sort-header').attr('data-direction', 4);
        switch (direction) {
            case '4':
                $(this).attr('data-direction', 3);
                current_icon.attr('class', 'icon-arrow-up');
                break;
            case '3':
                $(this).attr('data-direction', 0);
                current_icon.attr('class', 'icon-arrow-down');
                break;
            case '0':
                $(this).attr('data-direction', 4);
                current_icon.attr('class', 'icon-stop');
                break;
        }
        Pagers.setSort(pager_id, column_name, direction);
        Pagers.reload(pager_id);
    });
});


function PagerList() {
    this.pagers = new Object;
    this.pager_ids = new Array;

    this.reload = function(pager_id) {
        this.pagers[pager_id].reload();
    };

    this.loadPagers = function()
    {
        $this = this;
        $('.pager').each(function() {
            var pager_dom = $(this);
            var pager_id = pager_dom.attr('id');

            new_pager = new Pager(pager_id, pager_dom);
            new_pager.init();
            if (new_pager.rows !== undefined) {
                $this.pagers[new_pager.id] = new_pager;
                $this.pager_ids.push(new_pager.id);
            }
        });
    };

    this.fillRows = function() {
        $this = this;
        this.pager_ids.forEach(function(val) {
            var pager = $this.pagers[val];
            pager.plugRows();
        });
    };

    this.setSort = function(pager_id, column_name, direction) {
        this.pagers[pager_id].setSort(column_name, direction);
    };
}


function Pager(id, page) {
    var $this = this;
    this.id = id;
    this.page = page;
    this.sort_by = '';
    this.direction = 0;

    this.reload = function()
    {
        this.clearRows();
        this.loadData();
        this.plugRows();
    }

    this.clearRows = function()
    {
        $('#' + this.id + ' .pager-row').remove();
    }

    this.setSort = function(column_name, direction)
    {
        this.sort_by = column_name;
        this.direction = direction;
    };

    this.loadData = function() {
        var url = this.currentURL();
        var all_good = true;
        $.ajax({
            'url': url,
            'dataType': 'json',
            'data': {'pager_id': $this.id, 'sort_by': this.sort_by, 'direction': this.direction},
            'async': false,
            'success': function(data) {
                if (data.error || data.rows.length < 1) {
                    return;
                } else {
                    $this.rows = data.rows;
                }
            }
        });
    };

    this.loadRowTemplate = function() {
        this.row_template = $('#' + this.id + ' .pager-row');
        this.row_template.remove();
    };


    this.currentURL = function() {
        var unfiltered_url = document.URL;
        return unfiltered_url.replace(/\&.*$/g, '');
    };

    this.plugRows = function() {
        this.rows.forEach(function(row) {
            new_row = $this.row_template.clone();

            for (var key in row) {
                var cname = '.' + key;
                $(cname, new_row).html(row[key]);
            }
            $('.pager-body').append(new_row.outerHTML());
        });
    };

    this.init = function() {
        this.loadRowTemplate();
        this.loadData();
    };
}
