{ACTIVE}
<div id="property">
    <div id="photo-gallery">
        {NO_PHOTO}
        <!-- BEGIN photos -->
        <ul id="gallery">
            {PHOTOS}
        </ul><!-- END photos -->
        <!-- BEGIN admin --><div>{ADD_PHOTO} | {EDIT}</div><!-- END admin -->
        <div id="contact">
            <h3>{COMPANY_NAME}</h3>
            <p class="info"><!-- BEGIN company -->{GOOGLE_COMPANY}{COMPANY_ADDRESS}<br />
                <!-- END company --> {PHONE}<br />
                {EMAIL} <!-- BEGIN hours --><br />
                {HOURS}<!-- END hours --></p>
        </div>
    </div>
    <div id="property-info">
        <h2 id="name">{NAME}</h2>
        <p>{ADDRESS} {GOOGLE_MAP}</p>
        <!-- BEGIN desc --><h4>Description</h4>
        <p id="description">{DESCRIPTION}</p><!-- END desc -->
        <p><strong>Move-in date:</strong> {MOVE_IN_DATE}<br />
            <strong>Student preference:</strong> {STUDENT_TYPE}<br />
            <strong>Distance from campus:</strong> {CAMPUS_DISTANCE}<br />
            <strong>Bedrooms:</strong> {BEDROOMS}<br />
            <strong>Bathrooms:</strong> {BATHROOMS}<br />
            <strong>Windows in unit:</strong> {WINDOWS}
            <!-- BEGIN eff --><br /><span style="font-weight : bold; font-size : larger">{EFFICIENCY}</span><!-- END eff -->
        </p>
        <h3>Contract and Pricing</h3>
        <p><strong>Monthly Rent:</strong> ${MONTHLY_RENT} {LEASE_TYPE}<br />
            <strong>Contract length:</strong> {CONTRACT_LENGTH} {SUBLEASE}<br />
            <!-- BEGIN adm-fee --><strong>Administrative fee amount:</strong> {ADMIN_FEE} {ADMIN_FEE_REFUND}<br /><!-- END adm-fee -->
            <!-- BEGIN sec-fee --><strong>Security amount:</strong> {SECURITY_AMT} {SECURITY_REFUND}<br /><!-- END sec-fee -->
            <!-- BEGIN park-fee --><strong>Parking fee:</strong> {PARKING_FEE}<br /><!-- END park-fee -->
            <!-- BEGIN clean-fee --><strong>Cleaning fees:</strong> {CLEAN_FEE_AMT} {CLEAN_FEE_REFUND}<br /><!-- END clean-fee -->
            <!-- BEGIN other-fee --><strong>Other fees:</strong> {OTHER_FEES}<br /><!-- END other-fee -->
        </p>
        <h3>Pets</h3>
        <p><strong>Pets allowed:</strong> {PETS_ALLOWED} <!-- BEGIN pet-info --><br />
            <strong>Pet types:</strong> {PET_TYPES}<br />
            <strong>Pet fee:</strong> {PET_FEE}<br />
            <strong>Pet deposit:</strong> {PET_DEPOSIT}
            <!-- END pet-info --></p>
        <h3>Amenities and utilities</h3>
        <p>
            <strong>AppalCart:</strong> {APPALCART}<br />
            <strong>Parking per unit:</strong> {PARKING_PER_UNIT}<br />
            <strong>Clubhouse:</strong> {CLUBHOUSE}<br />
            <strong>Dishwasher:</strong> {DISHWASHER}<br />
            <strong>Internet:</strong> {INTERNET}<br />
            <strong>Television:</strong> {TV_TYPE}<br />
            <strong>Air Conditioning:</strong> {AIRCONDITIONING}<br />
            <strong>Laundry:</strong> {LAUNDRY}<!-- BEGIN heat --><br />
            <strong>Heat type:</strong> {HEAT_TYPE}<!-- END heat -->
        </p>
        <!-- BEGIN utilities -->
        <h3>Utility contributions</h3>
        <p>Manager will contribute up to an amount on the following utilities:<br />
            <!-- BEGIN water --><strong>Water:</strong> ${UTIL_WATER}<br /><!-- END water -->
            <!-- BEGIN trash --><strong>Trash:</strong> ${UTIL_TRASH}<br /><!-- END trash -->
            <!-- BEGIN power --><strong>Power:</strong> ${UTIL_POWER}<br /><!-- END power -->
            <!-- BEGIN fuel --><strong>Fuel:</strong> ${UTIL_FUEL}<br /><!-- END fuel -->
            <!-- BEGIN internet --><strong>Internet:</strong> ${UTIL_INTERNET}<br /><!-- END internet -->
            <!-- BEGIN phone --><strong>Phone:</strong> ${UTIL_PHONE}<!-- END phone --></p>
        <!-- END utilities -->


    </div>
    <div class="return-link">
        <a href="index.php">{ARROW} Back to list</a>
    </div>
</div>
<div id="photo-form" style="display : none"></div>