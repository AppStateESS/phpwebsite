<div class="menu" id="{MENU_ID}">
	<div>
	<!-- BEGIN menu_admin -->
		<div class="btn-group pull-right">
			<a class="btn dropdown-toggle btn-mini" data-toggle="dropdown" href="#"> <i class="icon-list">
			  </i> Menu Options <span class="caret"></span>
			</a>
			<ul class="dropdown-menu">
				<li>{PIN_LINK}</li>
				<li>{ADD_LINK}</li>
				<li>{ADD_SITE_LINK}</li>
				<li class="divider"></li>
				<li>{CLIP}</li>
			</ul>
		</div>
		<!-- END menu_admin -->
		<h5>{TITLE}</h5>
	</div>
	<ul id="sort-{MENU_ID}" class="nav nav-tabs nav-stacked">{LINKS}
	</ul>
	<div class="align-center">
		<!-- BEGIN pin -->
		{PIN_PAGE}<br />
		<!-- END pin -->
		{ADMIN_LINK}
	</div>
</div>