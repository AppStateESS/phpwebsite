{START_FORM}
<h2>General information</h2>
<table class="table table-striped">
     <tr>
        <td style="width: 160px">{NAME_LABEL}</td>
        <td>{NAME} <!--  BEGIN error1 -->
        <div class="error">
        <div class="arrow-left"></div>
        <div class="error-message">{NAME_ERROR}</div>
        </div>
        <!-- END error1 --></td>
    </tr>
    <tr>
        <td style="width: 160px">{MONTHLY_RENT_LABEL}</td>
        <td>{MONTHLY_RENT} <!--  BEGIN error2 -->
        <div class="error">
        <div class="arrow-left"></div>
        <div class="error-message">{MONTHLY_RENT_ERROR}</div>
        </div>
        <!-- END error2 --></td>
    </tr>
    <tr>
        <td>Smoking preference</td>
        <td>{SMOKING_1} {SMOKING_1_LABEL}<br />{SMOKING_2} {SMOKING_2_LABEL}<br />{SMOKING_3} {SMOKING_3_LABEL}</td>
    </tr>
    <tr>
        <td>Gender preference</td>
        <td>{GENDER_1} {GENDER_1_LABEL}<br />{GENDER_2} {GENDER_2_LABEL}<br />{GENDER_3} {GENDER_3_LABEL}</td>
    </tr>
    <tr>
        <td>Shared rooms</td>
        <td><span>{SHARE_BEDROOM} {SHARE_BEDROOM_LABEL}</span>
        <span style="margin-left : 20px">{SHARE_BATHROOM} {SHARE_BATHROOM_LABEL}</span></td>
    </tr>
    <tr>
        <td>{CONTRACT_LENGTH_LABEL}</td>
        <td>{CONTRACT_LENGTH}</td>
    </tr>
    <tr>
        <td>{SUBLEASE_LABEL}</td>
        <td>{SUBLEASE} Yes</td>
    </tr>
    <tr>
        <td>{MOVE_IN_DATE_LABEL}</td>
        <td>{MOVE_IN_DATE}</td>
    </tr>
    <tr>
        <td>{DESCRIPTION_LABEL}<br /><span style="font-size : 90%; font-style:italic">Personal contact information is NOT recommended.</span></td>
        <td>{DESCRIPTION}</td>
    </tr>
    <tr>
        <td>{PARKING_PER_UNIT_LABEL}</td>
        <td>{PARKING_PER_UNIT}</td>
    </tr>
</table>
<h2>Amenities / Conditionals</h2>
<table class="table table-striped">
    <tr>
        <td style="width: 160px">{INTERNET_TYPE_LABEL}</td>
        <td>{INTERNET_TYPE}</td>
    </tr>
    <tr>
        <td>{TV_TYPE_LABEL}</td>
        <td>{TV_TYPE}</td>
    </tr>
    <tr>
        <td>{LAUNDRY_TYPE_LABEL}</td>
        <td>{LAUNDRY_TYPE}</td>
    </tr>
    <tr>
        <td>{DISHWASHER_LABEL}</td>
        <td>{DISHWASHER} Yes</td>
    </tr>
    <tr>
        <td>{CAMPUS_DISTANCE_LABEL}</td>
        <td>{CAMPUS_DISTANCE}</td>
    </tr>
    <tr>
        <td>{APPALCART_LABEL}</td>
        <td>{APPALCART} Yes</td>
    </tr>
    <tr>
        <td>{CLUBHOUSE_LABEL}</td>
        <td>{CLUBHOUSE} Yes</td>
    </tr>
    <tr>
        <td>{WORKOUT_ROOM_LABEL}</td>
        <td>{WORKOUT_ROOM} Yes</td>
    </tr>
    <tr>
        <td>{TRASH_TYPE_LABEL}</td>
        <td>{TRASH_TYPE}</td>
    </tr>
    <tr>
        <td style="width: 160px">{PETS_ALLOWED_LABEL}</td>
        <td>{PETS_ALLOWED} Yes</td>
    </tr>
</table>
<div style="text-align: center; padding: 3px; background-color: #DAE3DE">{SUBMIT}</div>
{END_FORM}
