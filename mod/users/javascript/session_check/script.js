/* Original source: http://www.codeproject.com/Articles/711196/Session-Time-Out-Warning-Message-Using-jQuery-in-A */
//How frequently to check for session expiration in milliseconds
var sess_pollInterval = 30000;
var sess_interval_id;

// uncomment for testing
//sess_pollInterval = 10000;
//sess_expirationMinutes = 3;
//sess_warningMinutes = 1;

$(window).load(function () {
    var cs = new checkSession;
    cs.initSession();
});

function checkSession() {
    var t = this;
    this.poll_interval = sess_pollInterval;
    this.expiration_minutes = sess_expirationMinutes;
    this.minutes_until_warning = sess_warningMinutes;
    this.last_activity;
    this.interval_id;
    this.now;
    this.diff;
    this.diff_mins;
    this.warning_shown = false;

    this.initSession = function () {
        this.last_activity = new Date();
        this.SetInterval();
        this.InitializeDecisionButtons();
        $(document).bind('keypress.session', function (ed, e) {
            t.KeyPressed(ed, e);
        });
    }

    this.InitializeDecisionButtons = function ()
    {
        $('#stay-logged-in').click(function () {
            t.StayLoggedIn();
            t.RemoveAlert();
        });

        $('#logged-it-out').click(function () {
            t.LogOut();
        });
    };

    this.RemoveAlert = function () {
        $('#session-warning-alert').remove();
    };

    this.SetInterval = function () {
        this.interval_id = setInterval(function () {
            t.CheckInterval()
        }, t.poll_interval);
    };

    this.ClearInterval = function () {
        clearInterval(this.interval_id);
    };

    this.KeyPressed = function (ed, e) {
        this.last_activity = new Date();
    };

    this.LogOut = function () {
        window.location.href = 'index.php?module=users&action=user&command=logout';
    };

    this.ResetDiffTime = function () {
        this.now = new Date();
        this.diff = this.now - this.last_activity;
        this.diff_mins = (this.diff / 1000 / 60);
    };

    this.StayLoggedIn = function ()
    {
        $('#stay-logged-in').click(function () {
            $.get('index.php');
            t.ResetDiffTime();
            t.RemoveAlert();
        });
    };

    this.InsertWarning = function () {
        var minutes_left = this.expiration_minutes - this.minutes_until_warning;
        var session_warning = 'Your session will expire in <span id="timeout-countdown" style="font-weight:bold">' + minutes_left +
                ' minutes</span>. <button class="btn btn-sm btn-success" id="stay-logged-in">Stay logged in</button>&nbsp;' +
                '<button id="logged-it-out" class="btn btn-sm btn-danger">Logout</button>';
        $('body').prepend('<div id="session-warning-alert" class="text-center alert alert-danger">' + session_warning + '</div>');
    };

    this.CheckInterval = function () {
        this.ResetDiffTime();

        if (this.diff_mins >= this.minutes_until_warning) {
            //stop the timer
            //t.ClearInterval();
            if (t.warning_shown) {
                var countdown = t.expiration_minutes - t.diff_mins;
                if (countdown > 1) {
                    var minutes = Math.ceil(countdown) + ' minutes';
                    $('#timeout-countdown').html(minutes);
                } else if (countdown > 0) {
                    var seconds = Math.round(countdown * 60);
                    $('#timeout-countdown').html(seconds + ' seconds');
                } else {
                    t.RemoveAlert();
                    alert('Your session has run out. Copy your work before trying to submit a form.');
                    t.ClearInterval();
                }
            } else {
                t.warning_shown = true;
                t.InsertWarning();
            }
        }
    };
}
