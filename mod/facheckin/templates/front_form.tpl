<table style="width : 100%">
    <tr>
        <td id="banner-form-left">
            <form method="get" action="index.php?biteme=1" id="banner-fake">
            <h2>Check-In: Financial Aid Counseling</h2>
            <p><em>Banner ID (or swipe your Student ID Card)</em><br />
                {CUSTOMERID}
            </p>
            <p><label for="reason-id"><em>Reason for Visit</em></label><br />
                {REASON_ID}
                <input type="submit" name="checkin" value="Check In" id="banner-submit" />
            </p>
            </form>
        </td>
        <td id="banner-form-right">
            <div>
                <h2>No Student ID?</h2>
                <p>You will need a government-issued Photo ID to meet with your counselor. Please select an option below to check-in without a Student ID.</p>
                <input type="button" name="student-signin" value="I am (or will be) a student." id="student-signin" />
                <input type="button" name="parent-signin" value="I am a parent or guardian." id="parent-signin" />
                </p>
            </div>
        </td>
    </tr>
</table>
<div id="fa-message" style="display:none"></div>
<div id="student-info" style="display:none">
    <p><strong>Name: </strong> <span class="first-name"></span> <span class="last-name"></span><br />
        <strong>Email: </strong> <span class="email"></span><br />
        <strong>Reason for visit:</strong>
        <select name="reason_id" id="sub-reason"></select><br />
    </p>
    <p>
        <input type="button" name="submit_ticket" value="Continue" id="submit-ticket" disabled="disabled"  />
    </p>
</div>
<div id="no-student" style="display:none">
    <p>
        <label for="no-student-first-name">First Name</label><br />
        <input type="text" name="first_name" id="no-student-first-name" value="" />
    </p>
    <p>
        <label for="no-student-last-name">Last Name</label><br />
        <input type="text" name="last_name" id="no-student-last-name" value="" />
    </p>
    <p>
        <label for="no-student-email">Email</label><br />
        <input type="text" name="email" id="no-student-email" value="" />
    </p>
    <p>
        <label for="no-student-birthdate">Birth Date</label><br />
        <input type="text" name="birthdate" class="datepicker" id="no-student-birthdate" value="{DEFAULT_DATE}" />
    </p>
    <p>
        <label for="no-student-reason">Reason for Visit</label><br />
        <select name="reason_id" id="no-student-reason"></select>
    </p>
    <p>
        <input type="button" name="submit_ticket" value="Continue" id="no-student-submit" disabled="disabled"  />
    </p>
</div>
<div id="parent-signin-form" style="display:none">
    <p>
        <label for="parent-signin-first-name">Student's First Name</label><br />
        <input type="text" name="first_name" id="parent-signin-first-name" value="" />
    </p>
    <p>
        <label for="parent-signin-last-name">Student's Last Name</label><br />
        <input type="text" name="last_name" id="parent-signin-last-name" value="" />
    </p>
    <p>
        <label for="parent-signin-email">Student's Email</label><br />
        <input type="text" name="email" id="parent-signin-email" value="" />
    </p>
    <p>
        <label for="parent-signin-birthdate">Student's Birth Date</label><br />
        <input type="text" name="birthdate" class="datepicker" id="parent-signin-birthdate" value="{DEFAULT_DATE}" />
    </p>
    <p>
        <label for="parent-signin-name">Your name</label><br />
        <input type="text" name="parent_name" id="parent-signin-name" />
    </p>
    <p>
        <label for="parent-signin-reason">Reason for Visit</label><br />
        <select name="reason_id" id="parent-signin-reason"></select>
    </p>
    <p>
        <input type="button" name="submit_ticket" value="Continue" id="parent-signin-submit" disabled="disabled"  />
    </p>
</div>
<div id="waiting" style="display: none; border : 1px solid black; background-color : white; position : absolute; top : 25%; left: 25%;width : 500px; height : 500px; z-index : 999;text-align : center">
    <img src="{SOURCE_HTTP}mod/facheckin/img/ajax-loader.gif" style="margin-top : 40%" />
    <p style="position:absolute; bottom : 4px; left: 140px"><a href="./index.php">Stuck? Click here to reset the form</a></p>
</div>