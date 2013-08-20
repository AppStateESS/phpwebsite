var required = new Required;

$(window).load(function() {
    var error_free;
    $('input[required],textarea[required]').change(function() {
        var input = $(this);
        if ($('[type="text"]', input)) {
            required.checkText(input);
        }
    });

    $('select[required]').change(function() {
        var select = $(this);
        required.checkSelect(select);
    });

    $('.phpws-form').submit(function() {
        var all_is_well = true;
        $('[required]', this).each(function() {
            input = $(this);
            if (input.is('input')) {
                switch (input.attr('type')) {
                    case 'text':
                        if (!required.checkText(input)) {
                            all_is_well = false;
                        }
                        break;
                }
            } else if (input.is('select')) {
                if (!required.checkSelect(input)) {
                    all_is_well = false;
                }
            } else if (input.is('textarea')) {
                if (!required.checkText(input)) {
                    all_is_well = false;
                }
            }
        });
        return all_is_well;
    });
});

function Required() {
    this.checkText = function(input) {
        if (input.val().length < 1) {
            this.addEmptyError(input);
            return false;
        } else {
            this.removeError(input);
            return true;
        }
    };

    this.checkSelect = function(select) {
        var option = $('option:selected', select);
        if (option.val() < 1) {
            this.addSelectError(select);
            return false;
        } else {
            this.removeError(select);
            return true;
        }
    };

    this.addEmptyError = function(input) {
        input.after('<div class="required-error label label-important">Must not be left empty.</div>');
    };

    this.addSelectError = function(input) {
        input.after('<div class="required-error label label-important">Please select an option.</div>');
    };

    this.removeError = function(input) {
        input.next('.required-error').remove();
    };
}