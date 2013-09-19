var allow_refresh = true;
var banner = new Banner;
var suspend_scan = 0;
$(document).ready(function(){
    $('#waiting').hide();
    // start the banner object;
    banner.init();

    // Initialize the datepicker used in birthdate selection
    min_date = new Date;
    min_date.setFullYear(min_date.getFullYear()-90);
    max_date = new Date;
    max_date.setFullYear(max_date.getFullYear()-12);

    $.datepicker.formatDate( 'mm/dd/yyyy');
    $('.datepicker').datepicker({
        changeMonth : true,
        changeYear : true,
        yearRange : min_date.getFullYear() + ':' + max_date.getFullYear()
    });
});


function Student()
{
    var $this = this;

    var banner_id = null;
    var firstname = null;
    var lastname  = null;
    var email     = null;
    var ticketnum = null;
    var ticketid  = null;
    var guardian  = null;
    var reason_id = null;
    /**
     * Posting student without a banner account
     */
    this.postUnticketed = function()
    {
        $('#waiting').show();
        $.post('index.php',
        {
            module     : 'facheckin',
            uop        : 'post_no_ticket',
            first_name : $this.firstname,
            last_name  : $this.lastname,
            email      : $this.email,
            guardian   : $this.guardian,
            reason_id  : $this.reason_id
        },
        function(data){
            $('#waiting').hide();
            var greet = $this.firstname;
            if (!$this.guardian == '') {
                greet = $this.guardian;
            }
            if (data == 'true') {
                faMessage('Thank you, ' + greet, 'Please have a seat.<br />Someone will be with you shortly');
            } else {
                faMessage('Sorry...', 'There was an error creating your account. Please inform the front desk.');
            }
        });
    }

    this.post = function()
    {
        $('#waiting').show();
        //console.log('student.post');
        //console.log('student reason id is ' + $this.reason_id);
        $.post('index.php',
        {
            module     : 'facheckin',
            uop        : 'post_checkin',
            banner_id  : $this.banner_id,
            username   : $this.username,
            first_name : $this.firstname,
            last_name  : $this.lastname,
            email      : $this.email,
            guardian   : $this.guardian,
            reason_id  : $this.reason_id
        },
        function(data){
            $('#waiting').hide();
            var greet = $this.firstname;
            if (!$this.guardian == '') {
                greet = $this.guardian;
            }
            if (data == 'true') {
                faMessage('Thank you, ' + greet, 'Please have a seat.<br />Someone will be with you shortly');
            } else {
                faMessage('Sorry...', 'There was an error creating your account. Please inform the front desk.');
            }
        });
    }

    /**
     * Copies over the information received from the OTRS script. It will include
     * only the following:
     *
     * banner_id
     * firstname
     * lastname
     * email
     * ticketnum
     * errornum
     * error
     *
     * We ignore saving the error data in the student object
     **/
    this.copyData = function (data)
    {
        //console.log('student.copyData');
        $this.banner_id = data.banner_id;
        $this.username  = data.username;
        $this.firstname = data.firstname;
        $this.lastname  = data.lastname;
        $this.email     = data.email;
    }

}

function Banner()
{
    var $this = this;

    // Student object
    var student = null;

    // CardReader object
    var reader = null;

    // code returned after an id scan
    var banner_code = null;

    // stores the current reason pointer. This will changed dependent
    // on which for is open.
    var reason_node = null;

    this.init = function() {

        this.reason_node = $('select#reason-id')

        this.student = new Student;
        this.scanIt();

        $('#banner-fake').submit(function(){
            $this.bannerClick();
            return false;
        });

        // in this case, the user typed in their banner id into the text box

        $('#banner-submit').click(function() {
            $this.bannerClick();
            return false;
        });

        // initializes the check for users without an id
        $this.noStudentId();

        // initializes the check for parents without a student id
        $this.parentSignin();
    };

    this.bannerClick = function() {
        $this.student.banner_id = $('#customerid').val();
        if ($this.student.banner_id == '') {
            alert('Please enter your banner id number to continue');
            return;
        }
        $this.checkBannerId();
    };


    this.checkCompleted = function(jqxhr, status) {
        switch(status) {
            case 'timeout':
                $('#waiting').hide();
                alert('Your request timed out. Please try again.');
                window.location.reload();
                break;

            case 'parsererror':
            case 'error':
            case 'abort':
                $('#waiting').hide();
                alert('An error occurred while trying to access your information. Please try again.');
                window.location.reload();
                break;

            default:
                $('#waiting').hide();
                break;
        }
    };


    this.checkSuccess = function(data) {
        //console.log(data);
        // check returned JSON for error
        $('#waiting').hide();
        if (data.errornum > 0 ) {
            switch (data.errornum) {
                case 1:
                    alert("Sorry...\nWe could not find an account with the entered banner id number.\nPlease check the number and enter it again or click a button in\nthe No Student ID field.");
                    break;

                case 2:
                case 6:
                    alert("Sorry...\nAn error occurred that requires administrative attention.");
                    break;

                default:
                    alert('Sorry... an error occurred. Please try again or speak to the front desk.');
            }
            window.location.reload();
            return;
        }
        // JSON did not have an error. Now copy the data to the student object
        $this.student.copyData(data);

        if ($this.reason_node.val() == 0) {
            $this.yankReason($('#sub-reason'));
            $this.display();
        } else {
            //console.log('banner checked copying reason value');
            $this.student.reason_id = $this.reason_node.val();
            $this.student.post();
        }
    }

    /**
     * Uses a JSON inquiry on the facheckin module to see if the banner_id is
     * legitimate.
     **/
    this.checkBannerId = function() {
        if (suspend_scan == 1) {
            return;
        }
        $('#waiting').show();
        $.ajax({
            url: 'index.php',
            data: {
                module    : 'facheckin',
                uop       : 'new_ticket',
                banner_id : $this.student.banner_id
            },
            cache : false,
            complete : function(jqxrh, status) {
                $this.checkCompleted(jqxrh, status)
            },
            dataType : 'json',
            success : function(json) {
                $this.checkSuccess(json)
            }
        });

        suspend_scan = 1;
    };

    this.display  = function()
    {
        //console.log('display');
        $('#student-info .first-name').html(this.student.firstname);
        $('#student-info .last-name').html(this.student.lastname);
        $('#student-info .email').html(this.student.email);

        $('#student-info').dialog({
            minWidth:600,
            minHeight:200,
            title : 'Choose reason for visit to continue',
            modal:true,
            open: $this.checkSelect(),
            close : function(){$this.checkClose()}
        });
    }

    this.checkClose = function() {
        if (allow_refresh) {
            window.location.reload();
        }
    }


    this.parentSignin = function() {
        $('input#parent-signin').click(function(){
            $this.yankReason($('#parent-signin-reason'));
            $('#parent-signin-form').dialog({
                title : 'Check-In as Parent or Guardian',
                minWidth:600,
                minHeight:200,
                modal:true,
                close : function(){$this.checkClose()}
            });
        });

        $('div#parent-signin-form input, div#parent-signin-form select').change(function(){
            $this.parentAllowContinue();
        });

        $('#parent-signin-submit').click(function(){
            allow_refresh = false;
            $this.student.firstname = $('input#parent-signin-first-name').val();
            $this.student.lastname = $('input#parent-signin-last-name').val();
            $this.student.birthdate = $('input#parent-signin-birthdate').val();
            $this.student.guardian = $('input#parent-signin-name').val();
            $this.student.reason_id = $('select#parent-signin-reason').val();
            $this.student.email = $('input#parent-signin-email').val();
            allow_refresh = false;
            $('#parent-signin-form').dialog('close');
            $('#waiting').show();

            $.getJSON('index.php', {
                module     : 'facheckin',
                uop        : 'no_student_id',
                reason_id  : $this.reason_node.val(),
                first_name : $this.student.firstname,
                last_name  : $this.student.lastname,
                birthdate  : $this.student.birthdate,
                email      : $this.student.email,
                guardian   : $this.student.guardian
            }, function(data) {
                $('#waiting').hide();
                $this.reason_node = $('#parent-signin-reason');
                if (data.banner_id == null) {
                    $this.student.postUnticketed();
                } else {
                    $this.student.copyData(data);
                    $this.student.post();
                }
            }
            );
        });
    };

    this.noStudentId = function() {
        $('input#student-signin').click(function(){
            $this.yankReason($('#no-student-reason'));
            $('#no-student').dialog({
                title : 'Check-In Without an ID',
                minWidth:600,
                minHeight:200,
                modal:true,
                close : function() {$this.checkClose()}
            });
        });

        $('div#no-student input, div#no-student select').change(function(){
            $this.noStudentAllowContinue();
        });

        $('#no-student-submit').click(function(){
            allow_refresh = false;
            $this.student.firstname = $('input#no-student-first-name').val();
            $this.student.lastname = $('input#no-student-last-name').val();
            $this.student.birthdate = $('input#no-student-birthdate').val();
            $this.student.reason_id = $('select#no-student-reason').val();
            $this.student.email = $('input#no-student-email').val();
            allow_refresh = false;
            $('#no-student').dialog('close');
            $('#waiting').show();
            $.getJSON('index.php', {
                module : 'facheckin',
                uop: 'no_student_id',
                reason_id : $this.reason_node.val(),
                first_name : $this.student.firstname,
                last_name  : $this.student.lastname,
                birthdate  : $this.student.birthdate,
                email : $this.student.email
            }, function(data) {
                $('#waiting').hide();
                $this.reason_node = $('#no-student-reason');
                if (data.banner_id == null) {
                    $this.student.postUnticketed();
                } else {
                    $this.student.copyData(data);
                    $this.student.post();
                }
            }
            );
        });
    };

    this.noStudentAllowContinue = function() {
        first_name = $('input#no-student-first-name').val();
        last_name = $('input#no-student-last-name').val();
        birthdate = $('input#no-student-birthdate').val();
        reason_id = $('select#no-student-reason').val();
        email = $('input#no-student-email').val();

        if (first_name != '' && last_name != '' && birthdate != '' && email && reason_id > 0) {
            $('#no-student-submit').attr('disabled', false);
        } else {
            $('#no-student-submit').attr('disabled', true);
        }

    }

    this.parentAllowContinue = function() {
        //console.log('parentAllowContinue');
        first_name = $('input#parent-signin-first-name').val();
        last_name = $('input#parent-signin-last-name').val();
        birthdate = $('input#parent-signin-birthdate').val();
        reason_id = $('select#parent-signin-reason').val();
        parent_name = $('input#parent-signin-name').val();
        email = $('input#parent-signin-email').val();

        if (first_name != '' && last_name != '' && birthdate != '' && parent_name != '' && email && reason_id > 0) {
            $('#parent-signin-submit').attr('disabled', false);
        } else {
            $('#parent-signin-submit').attr('disabled', true);
        }

    }


    /**
     * Copied the options from the main select into the window select
     */
    this.yankReason = function(copy_to)
    {
        copy_to.html($('#reason-id').html());
        this.reason_node = copy_to;
    }


    /**
     * Routine to use card scanner on page. Successful swipe puts banner_id into
     * the student object and the banner_code (which comes after the id) into
     * the current object variable.
     */
    this.scanIt = function ()
    {
        this.reader = new CardReader();
        //var textbox = $('#banner-id');
        this.reader.observe(document);

        // Errback in case of a reading error
        this.reader.cardError(function () {
            alert('A read error occurred');
        });
        // Callback in case of a successful reading operation
        this.reader.cardRead(function (value) {
            var banner_array = value.split("=");
            $this.student.banner_id = banner_array[0];
            $this.banner_code = banner_array[1];
            $this.checkBannerId();
        });
    }

    this.checkSelect = function ()
    {
        //console.log('checkSelect');
        var select_id;
        var submit_ticket = $('#submit-ticket');

        //this.reason_node

        $('#sub-reason').change(function(){
            select_id = $(this).find('option:selected').val();
            if (select_id > 0) {
                submit_ticket.attr('disabled', false);
            } else {
                submit_ticket.attr('disabled', true);
            }
        });
        submit_ticket.click(function(){
            allow_refresh = false;
            $('#student-info').dialog('close');
            $this.student.reason_id = select_id;
            $this.student.post();
        });
    }
}

faMessage = function(title, message) {
    $('#fa-message').html(message);
    $('#fa-message').dialog({
        buttons : {
            "Ok": function(){
                location.reload();
            }
        },
        title: title,
        modal : true
    });
    window.setTimeout(function() {
        location.reload();
    }, 5000);
};