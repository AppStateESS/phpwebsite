jQuery.fn.outerHTML = function(s) {
    return (s)
            ? this.before(s).remove()
            : jQuery("<p>").append(this.eq(0).clone()).html();
};

$(window).load(function() {
    Pagers = new PagerList;
    Pagers.loadPagers();
    Pagers.fillRows();
});

function PagerList() {
    this.pagers = new Object;
    this.pager_ids = new Array;

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
}


function Pager(id, page) {
    var $this = this;
    this.id = id;
    this.page = page;
    //this.column_names = new Array();


    this.loadData = function() {
        var url = this.currentURL();
        var all_good = true;
        $.ajax({
            'url': url,
            'dataType': 'json',
            'data': {'pager_id': $this.id},
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
        //this.columns = this.row_template.find('.pager-column');
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
        var result = this.loadData();
        this.loadData();
    };
}
