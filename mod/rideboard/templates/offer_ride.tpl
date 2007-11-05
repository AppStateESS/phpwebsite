{START_FORM}
<p>{ALLOW_SMOKING} {ALLOW_SMOKING_LABEL}</p>
<div class="section">
<div class="left">{S_LOCATION_LABEL} {S_LOCATION}</div>
<div>{D_LOCATION_LABEL} {D_LOCATION}</div>
</div>

<div class="section">
<div class="left"><label>{DEPARTURE_TIME_LABEL}</label><br />
{DEPARTURE_TIME_DAY} {DEPARTURE_TIME_MONTH} {DEPARTURE_TIME_YEAR}{JS_DEP}
</div>
<div><label>{RETURN_TIME_LABEL}</label><br />
{RETURN_TIME_DAY} {RETURN_TIME_MONTH} {RETURN_TIME_YEAR}{JS_RET}
</div>
</div>

<label>{GENDER_PREF_LABEL}</label>
<p class="radio-buttons">
{GENDER_PREF_1} {GENDER_PREF_1_LABEL}<br />
{GENDER_PREF_2} {GENDER_PREF_2_LABEL}<br />
{GENDER_PREF_3} {GENDER_PREF_3_LABEL}
</p>

<p>
{DETOUR_DISTANCE_LABEL} {DETOUR_DISTANCE}
</p>

{COMMENTS_LABEL}<br />
{COMMENTS}

<p style="text-align : center">{SUBMIT}</p>
{END_FORM}
