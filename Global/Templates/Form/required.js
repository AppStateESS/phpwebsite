var required = new Required;

$(window).load(function() {
    var error_free;
    $('input[required],textarea[required]').change(function() {
        var input = $(this);
        required.checkInput(input);
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
                if (!required.checkInput(input)) {
                    all_is_well = false;
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

    this.checkInput = function(input) {
        switch (input.attr('type')) {
            case 'text':
                return this.checkText(input);

            case 'email':
                return this.checkEmail(input);
        }
    };

    this.checkText = function(input) {
        if (input.val().length < 1) {
            this.addEmptyError(input);
            return false;
        } else {
            this.removeError(input);
            return true;
        }
    };

    this.checkEmail = function(input) {
        if (!this.checkText(input)) {
            return false;
        }

        var match = input.val().match(/^[\w.%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/i);
        if (match) {
            this.removeError(input);
            return true;
        } else {
            input.after('<div class="required-error label label-important">Email address not formatted correctly.</div>');
            return false;
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