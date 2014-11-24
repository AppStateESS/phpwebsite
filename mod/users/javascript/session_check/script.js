/* Original source: http://www.codeproject.com/Articles/711196/Session-Time-Out-Warning-Message-Using-jQuery-in-A */
//How frequently to check for session expiration in milliseconds
var sess_pollInterval = 60000;
var sess_intervalID;
var sess_lastActivity;

// uncomment for testing
sess_pollInterval = 1000;
sess_expirationMinutes = 3;
sess_warningMinutes = 1;

$(window).load(function () {
    initSession();
});

function initSession() {
    sess_lastActivity = new Date();
    sessSetInterval();
    $(document).bind('keypress.session', function (ed, e) {
        sessKeyPressed(ed, e);
    });
}
function sessSetInterval() {
    sess_intervalID = setInterval('sessInterval()', sess_pollInterval);
}
function sessClearInterval() {
    clearInterval(sess_intervalID);

}
function sessKeyPressed(ed, e) {
    sess_lastActivity = new Date();
}
function sessLogOut() {
    window.location.href = 'index.php?module=users&action=user&command=logout';
}

function stayLoggedIn()
{
    $('#stay-logged-in').click(function () {
        $.get('index.php');
        now = new Date();
        diff = now - sess_lastActivity;
        diffMins = (diff / 1000 / 60);
    });
}

function sessInterval() {
    var now = new Date();
    //get milliseconds of differneces
    var diff = now - sess_lastActivity;
    //get minutes between differences
    var diffMins = (diff / 1000 / 60);
    console.log(diffMins);
    console.log(sess_warningMinutes);
    if (diffMins >= sess_warningMinutes) {
        //warn before expiring
        //stop the timer
        sessClearInterval();
        //prompt for attention
        var session_warning = 'Your session will expire in ' + (sess_expirationMinutes - sess_warningMinutes) +
                ' minutes. <button class="btn btn-sm btn-success" id="stay-logged-in">Stay logged in</button> <button id="logged-it-out" class="btn btn-sm btn-danger">Logout</button>'
        $('body').prepend('<div class="text-center alert alert-danger">' + session_warning + '</div>');

        /*
         var active = confirm();
         if (active === true) {
         $.get('index.php');
         now = new Date();
         diff = now - sess_lastActivity;
         diffMins = (diff / 1000 / 60);
         if (diffMins > sess_expirationMinutes) {
         sessLogOut();
         }
         else {
         initSession();
         sessSetInterval();
         sess_lastActivity = new Date();
         }
         }
         else {
         sessLogOut();
         }
         */
    }
}