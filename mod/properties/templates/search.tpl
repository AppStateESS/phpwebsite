<script type="text/javascript">

$(document).ready(function(){
    $(".panel-heading").addClass("collapsed");
});

</script>
<div id="search-criteria">
    <h3>Search criteria</h3>
    <p><em>Narrow your search by choosing one or more search criteria below.</em></p>
    <!-- BEGIN criteria --><ul class="list-group"><li class="list-group-item">{CRITERIA}</li></ul><!-- END criteria -->

    <div>{CLEAR}</div>
    <hr />
    {START_FORM}

<div class="panel-group" id="student-accordion">
	<div class="panel panel-default">
	<div class="panel-heading" data-toggle="collapse" data-parent="#student-accordion" data-target="#pcollapse1">
		<h4 class="panel-title btn btn-default btn-block accordion-toggle">
			Student Preference
		</h4>
	  </div>
	  <div id="pcollapse1" class="panel-collapse collapse">
		<div class="panel-body">
		  {STUDENT_TYPE}
		</div>
	  </div>
	</div>
</div>
<div class="panel-group" id="distance-accordion">
	<div class="panel panel-default">
	  <div class="panel-heading" data-toggle="collapse" data-parent="#distance-accordion" href="#pcollapse2">
		<h4 class="panel-title btn btn-default btn-block accordion-toggle">
		Distance from campus
		</h4>
	  </div>
	  <div id="pcollapse2" class="panel-collapse collapse">
		<div class="panel-body">
		  {DISTANCE_OPTIONS}
		</div>
	  </div>
</div>
</div>
<div class="panel-group" id="price-accordion">
	<div class="panel panel-default">	  
	  <div class="panel-heading" data-toggle="collapse" data-parent="#price-accordion" href="#pcollapse3">
		<h4 class="panel-title btn btn-default btn-block accordion-toggle">
			Price Range
		</h4>
	  </div>
	  <div id="pcollapse3" class="panel-collapse collapse">
		<div class="panel-body">
		  {PRICE_OPTIONS}
		</div>
	  </div>
	  </div>
</div>
<div class="panel-group" id="bedrooms-accordion">
	<div class="panel panel-default">
	  <div class="panel-heading" data-toggle="collapse" data-parent="#bedrooms-accordion" href="#pcollapse4">
		<h4 class="panel-title btn btn-default btn-block accordion-toggle">
			Bedrooms
		</h4>
	  </div>
	  <div id="pcollapse4" class="panel-collapse collapse">
		<div class="panel-body">
		  {BEDROOM_CHOICE}
		</div>
	  </div>
	  </div>
</div>
<div class="panel-group" id="bathrooms-accordion">
	<div class="panel panel-default">
	  <div class="panel-heading" data-toggle="collapse" data-parent="#bathrooms-accordion" href="#pcollapse5">
		<h4 class="panel-title btn btn-default btn-block accordion-toggle">
			Bathrooms
		</h4>
	  </div>
	  <div id="pcollapse5" class="panel-collapse collapse">
		<div class="panel-body">
		  {BATHROOM_CHOICE}
		</div>
	  </div>
	  </div>
</div>
<div class="panel-group" id="feature-accordion">
	<div class="panel panel-default">
	  <div class="panel-heading" data-toggle="collapse" data-parent="#feature-accordion" href="#pcollapse6">
		<h4 class="panel-title btn btn-default btn-block accordion-toggle">
			Features/Amenities
		</h4>
	  </div>
	  <div id="pcollapse6" class="panel-collapse collapse">
		<div class="panel-body">
		  {FEATURES}
		</div>
	  </div>
	  </div>
</div>
<div class="panel-group" id="manager-accordion">
	<div class="panel panel-default">
	  <div class="panel-heading" data-toggle="collapse" data-parent="#manager-accordion" href="#pcollapse7">
		<h4 class="panel-title btn btn-default btn-block accordion-toggle">
			Manager
		</h4>
	  </div>
	  <div id="pcollapse7" class="panel-collapse collapse">
		<div class="panel-body">
		  {MANAGER} {MANAGER_SUBMIT}
		</div>
	  </div>
	  </div>
</div>
<div class="panel-group" id="properties-accordion">
	<div class="panel panel-default">
	  <div class="panel-heading" data-toggle="collapse" data-parent="#properties-accordion" href="#pcollapse8">
		<h4 class="panel-title btn btn-default btn-block accordion-toggle">
			Property name
		</h4>
	  </div>
	  <div id="pcollapse8" class="panel-collapse collapse">
		<div class="panel-body">
		  {PROPERTY_NAME} {PROPERTY_NAME_SUBMIT}
		</div>
	  </div>
	  </div>
</div>
<div class="panel-group" id="contract-accordion">
	<div class="panel panel-default">
	  <div class="panel-heading" data-toggle="collapse" data-parent="#contract-accordion" href="#pcollapse9">
		<h4 class="panel-title btn btn-default btn-block accordion-toggle">
			Contract
		</h4>
	  </div>
	  <div id="pcollapse9" class="panel-collapse collapse">
		<div class="panel-body">
		  {SUBLEASE}<br />{NOSUB}
		</div>
	  </div>
        </div>
    </div>
    {END_FORM}
</div>

