<h3>Search criteria</h3>
<p>{CRITERIA}</p>

<div style="text-align : center; border-top : 1px dotted gray">{CLEAR}</div>
<hr />
{START_FORM}

<div class="accordion" id="properties-accordion">
    <div class="accordion-group">
        <div class="accordion-heading">
            <button type="button" class="btn btn-default" data-toggle="collapse" data-target="#pcollapse1">Preferences</button>
        </div>
        <div id="pcollapse1" class="accordion-body collapse">
            <div class="accordion-inner"><ul><li>{GENDER_MALE}</li><li>{GENDER_FEMALE}</li></ul>
                <ul><li>{SMOKING_NO}</li><li>{SMOKING_YES}</li></ul>
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
            <button type="button" class="btn btn-default accordion-toggle" data-toggle="collapse" data-target="#pcollapse2">
                Price range
            </button>
        </div>
        <div id="pcollapse2" class="accordion-body collapse">
            <div class="accordion-inner">{PRICE_OPTIONS}
            </div>
        </div>
    </div>
    <div class="accordion-group">
        <div class="accordion-heading">
            <button type="button" class="btn btn-default accordion-toggle" data-toggle="collapse" data-target="#pcollapse2">
                Shared rooms
            </button>
        </div>
        <div id="pcollapse2" class="accordion-body collapse">
            <div class="accordion-inner">{BEDROOM_CHOICE}<br />
                {BATHROOM_CHOICE}
            </div>
        </div>
    </div>
    <div class="accordion-group">
        <div class="accordion-heading">
            <button type="button" class="btn btn-default accordion-toggle" data-toggle="collapse" data-target="#pcollapse2">
                Features/Amenities
            </button>
        </div>
        <div id="pcollapse2" class="accordion-body collapse">
            <div class="accordion-inner">{FEATURES}
            </div>
        </div>
    </div>
    <div class="accordion-group">
        <div class="accordion-heading">
            <button type="button" class="btn btn-default accordion-toggle" data-toggle="collapse" data-target="#pcollapse2">
                Contract
            </button>
        </div>
        <div id="pcollapse2" class="accordion-body collapse">
            <div class="accordion-inner">{SUBLEASE}<br />{NOSUB}
            </div>
        </div>
    </div>
</div>

{END_FORM}