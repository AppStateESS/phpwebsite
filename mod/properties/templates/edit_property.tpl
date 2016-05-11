{START_FORM}
<h2>General information</h2>
<table class="table table-striped">
    <!-- BEGIN contact --><tr>
        <td>{CONTACT_ID_LABEL}</td>
        <td>{CONTACT_ID}</td>
    </tr><!-- END contact -->
    <tr>
        <td style="width: 160px">{NAME_LABEL}</td>
        <td>{NAME} <!--  BEGIN error1 --><span class="label label-danger">{NAME_ERROR}</span><!-- END error1 --></td>
    </tr>
    <tr>
        <td>{MONTHLY_RENT_LABEL}</td>
        <td>${MONTHLY_RENT} <!--  BEGIN error2 --><span class="label label-danger">{MONTHLY_RENT_ERROR}</span><!-- END error2 --></td>
    </tr>
    <tr>
        <td><strong>Lease type</strong></td>
        <td>{LEASE_TYPE_1} {LEASE_TYPE_1_LABEL}<br />{LEASE_TYPE_2} {LEASE_TYPE_2_LABEL}</td>
    </tr>
    <tr>
        <td>{UTILITIES_INC_LABEL}</td>
        <td>{UTILITIES_INC} Yes</td>
    </tr>
    <tr>
        <td>{ADDRESS_LABEL}</td>
        <td>{ADDRESS} <!--  BEGIN error3 --><span class="label label-danger">{ADDRESS_ERROR}</span><!-- END error3 --></td>
    </tr>
    <tr>
        <td>{WINDOW_NUMBER_LABEL}</td>
        <td>{WINDOW_NUMBER} Yes</td>
    </tr>
    <tr>
        <td>{EFFICIENCY_LABEL}</td>
        <td>{EFFICIENCY} Yes</td>
    </tr>
    <tr>
        <td><strong>Rooms</strong></td>
        <td>
            <div>{BEDROOM_NO_LABEL} {BEDROOM_NO}<!--  BEGIN error4 --> <span class="label label-danger">{BEDROOM_NO_ERROR}</span><!-- END error4 -->
</div>
            <div>{BATHROOM_NO_LABEL} {BATHROOM_NO}</div>
        </td>
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
        <td><strong>Student preference</strong></td>
        <td>{STUDENT_TYPE_1} {STUDENT_TYPE_1_LABEL}<br />
            {STUDENT_TYPE_2} {STUDENT_TYPE_2_LABEL}<br />
            {STUDENT_TYPE_3} {STUDENT_TYPE_3_LABEL}
        </td>
    </tr>
    <tr>
        <td>{DESCRIPTION_LABEL}</td>
        <td>{DESCRIPTION}</td>
    </tr>
    <tr>
        <td>{PARKING_PER_UNIT_LABEL}</td>
        <td>{PARKING_PER_UNIT}</td>
    </tr>
</table>
<div style="text-align: center; padding: 3px; background-color: #DAE3DE">{SUBMIT}</div>
<h2>Amenities and utilities</h2>
<table class="table table-striped">
    <tr>
        <td style="width: 160px">
            <strong>Heating system</strong>
        </td>
        <td>
            <div class="pull-left" style="margin-right : 1em">
                {HEAT_TYPE_1} {HEAT_TYPE_1_LABEL}<br />
                {HEAT_TYPE_2} {HEAT_TYPE_2_LABEL}<br />
                {HEAT_TYPE_3} {HEAT_TYPE_3_LABEL}<br />
                {HEAT_TYPE_4} {HEAT_TYPE_4_LABEL}
            </div>
            <div class="pull-left">
                {HEAT_TYPE_5} {HEAT_TYPE_5_LABEL}<br />
                {HEAT_TYPE_6} {HEAT_TYPE_6_LABEL}<br />
                {HEAT_TYPE_7} {HEAT_TYPE_7_LABEL}
            </div>
        </td>
    </tr>
    <tr>
        <td>{INTERNET_TYPE_LABEL}</td>
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
        <td>{CAMPUS_DISTANCE_LABEL}</td>
        <td>{CAMPUS_DISTANCE}</td>
    </tr>
    <tr>
        <td>{FURNISHED_LABEL}</td>
        <td>{FURNISHED} Yes</td>
    </tr>
    <tr>
        <td>{DISHWASHER_LABEL}</td>
        <td>{DISHWASHER} Yes</td>
    </tr>
    <tr>
        <td>{AIRCONDITIONING_LABEL}</td>
        <td>{AIRCONDITIONING} Yes</td>
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
</table>
<div style="text-align: center; padding: 3px; background-color: #DAE3DE">{SUBMIT}</div>
<h2>Conditionals</h2>
<table class="table table-striped">
    <tr>
        <td style="width: 160px">{PETS_ALLOWED_LABEL}</td>
        <td>{PETS_ALLOWED} Yes</td>
    </tr>
    <tr>
        <td>{PET_TYPE_LABEL}</td>
        <td>{PET_TYPE}</td>
    </tr>
    <tr>
        <td>{PET_FEE_LABEL}</td>
        <td>{PET_FEE}<br />
            (Nonrefundable)</td>
    </tr>
    <tr>
        <td>{PET_DEPOSIT_LABEL}</td>
        <td>{PET_DEPOSIT}<br />(Refundable)</td>
    </tr>
</table>
<div style="text-align: center; padding: 3px; background-color: #DAE3DE">{SUBMIT}</div>
<h2>Other fees</h2>
<table class="table table-striped">
    <tr>
        <td style="width: 160px">{SECURITY_AMT_LABEL}</td>
        <td>{SECURITY_AMT}<br />
            {SECURITY_REFUND}{SECURITY_REFUND_LABEL}</td>
    </tr>
    <tr>
        <td>{ADMIN_FEE_AMT_LABEL}</td>
        <td>{ADMIN_FEE_AMT}<br />
            {ADMIN_FEE_REFUND} {ADMIN_FEE_REFUND_LABEL}</td>
    </tr>
    <tr>
        <td>{CLEAN_FEE_AMT_LABEL}</td>
        <td>{CLEAN_FEE_AMT}<br />
            {CLEAN_FEE_REFUND} {CLEAN_FEE_REFUND_LABEL}</td>
    </tr>
    <tr>
        <td>{PARKING_FEE_LABEL}</td>
        <td>{PARKING_FEE}</td>
    </tr>
    <tr>
        <td>{OTHER_FEES_LABEL}</td>
        <td>{OTHER_FEES}</td>
    </tr>
</table>
<div style="text-align: center; padding: 3px; background-color: #DAE3DE">{SUBMIT}</div>
<h2>Utility imbursement</h2>
If you pay a portion of one of the following utilities, please enter that
amount.
<table class="table table-striped">
    <tr>
        <td style="width: 160px">{UTIL_CABLE_LABEL}</td>
        <td>{UTIL_CABLE}</td>
    </tr>
    <tr>
        <td>{UTIL_FUEL_LABEL}</td>
        <td>{UTIL_FUEL}</td>
    </tr>
    <tr>
        <td>{UTIL_INTERNET_LABEL}</td>
        <td>{UTIL_INTERNET}</td>
    </tr>
    <tr>
        <td>{UTIL_PHONE_LABEL}</td>
        <td>{UTIL_PHONE}</td>
    </tr>
    <tr>
        <td>{UTIL_POWER_LABEL}</td>
        <td>{UTIL_POWER}</td>
    </tr>
    <tr>
        <td>{UTIL_TRASH_LABEL}</td>
        <td>{UTIL_TRASH}
    </tr>
    <tr>
        <td>{UTIL_WATER_LABEL}</td>
        <td>{UTIL_WATER}</td>
    </tr>
</table>
{SUBMIT} {END_FORM}
