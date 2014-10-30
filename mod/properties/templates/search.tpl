<div id="search-criteria">
    <h3>Search criteria</h3>
    <p><em>Narrow your search by choosing one or more search criteria below.</em></p>
    <!-- BEGIN criteria --><ul><li>{CRITERIA}</li></ul><!-- END criteria -->

    <div>{CLEAR}</div>
    <hr />
    {START_FORM}

    <div class="accordion" id="properties-accordion">
        <div class="accordion-group">
            <div class="accordion-heading">
                <button type="button" class="btn btn-default" data-toggle="collapse" data-target="#pcollapse1">Student Preference</button>
            </div>
            <div id="pcollapse1" class="accordion-body collapse">
                <div class="accordion-inner">{STUDENT_TYPE}
                </div>
            </div>
        </div>
        <div class="accordion-group">
            <div class="accordion-heading">
                <button type="button" class="btn btn-default accordion-toggle" data-toggle="collapse" data-target="#pcollapse2">
                    Distance from campus
                </button>
            </div>
            <div id="pcollapse2" class="accordion-body collapse">
                <div class="accordion-inner">{DISTANCE_OPTIONS}
                </div>
            </div>
        </div>
        <div class="accordion-group">
            <div class="accordion-heading">
                <button type="button" class="btn btn-default accordion-toggle" data-toggle="collapse" data-target="#pcollapse3">
                    Price range
                </button>
            </div>
            <div id="pcollapse3" class="accordion-body collapse">
                <div class="accordion-inner">{PRICE_OPTIONS}
                </div>
            </div>
        </div>
        <div class="accordion-group">
            <div class="accordion-heading">
                <button type="button" class="btn btn-default accordion-toggle" data-toggle="collapse" data-target="#pcollapse4">
                    Bedrooms
                </button>
            </div>
            <div id="pcollapse4" class="accordion-body collapse">
                <div class="accordion-inner">{BEDROOM_CHOICE}
                </div>
            </div>
        </div>
        <div class="accordion-group">
            <div class="accordion-heading">
                <button type="button" class="btn btn-default accordion-toggle" data-toggle="collapse" data-target="#pcollapse5">
                    Bathrooms
                </button>
            </div>
            <div id="pcollapse5" class="accordion-body collapse">
                <div class="accordion-inner">{BATHROOM_CHOICE}
                </div>
            </div>
        </div>
        <div class="accordion-group">
            <div class="accordion-heading">
                <button type="button" class="btn btn-default accordion-toggle" data-toggle="collapse" data-target="#pcollapse6">
                    Features/Amenities
                </button>
            </div>
            <div id="pcollapse6" class="accordion-body collapse">
                <div class="accordion-inner">{FEATURES}
                </div>
            </div>
        </div>
        <div class="accordion-group">
            <div class="accordion-heading">
                <button type="button" class="btn btn-default accordion-toggle" data-toggle="collapse" data-target="#pcollapse7">
                    Manager
                </button>
            </div>
            <div id="pcollapse7" class="accordion-body collapse">
                <div class="accordion-inner">{MANAGER} {MANAGER_SUBMIT}
                </div>
            </div>
        </div>
        <div class="accordion-group">
            <div class="accordion-heading">
                <button type="button" class="btn btn-default accordion-toggle" data-toggle="collapse" data-target="#pcollapse8">
                    Property name
                </button>
            </div>
            <div id="pcollapse8" class="accordion-body collapse">
                <div class="accordion-inner">{PROPERTY_NAME} {PROPERTY_NAME_SUBMIT}
                </div>
            </div>
        </div>
        <div class="accordion-group">
            <div class="accordion-heading">
                <button type="button" class="btn btn-default accordion-toggle" data-toggle="collapse" data-target="#pcollapse9">
                    Contract
                </button>
            </div>
            <div id="pcollapse9" class="accordion-body collapse">
                <div class="accordion-inner">{SUBLEASE}<br />{NOSUB}
                </div>
            </div>
        </div>
    </div>
    {END_FORM}
</div>

