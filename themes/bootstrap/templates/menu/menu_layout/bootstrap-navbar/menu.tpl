<div class="menu" id="{MENU_ID}">
	<!-- BEGIN menu_admin -->
		<div class="btn-group pull-left">	
		<a id="modal-773796" href="#menu-modal" role="button" class="btn" data-toggle="modal"><i class="icon-list">
			  </i> Menu Options <span class="caret"></span></a>
		</div>
<div id="menu-modal" class="modal hide fade" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<div class="modal-header">
					 <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h3 id="myModalLabel">
						Menu Options
					</h3>
				</div>
				<div class="modal-body">
					<ul>
				<li>{PIN_LINK}</li>
				<li>{ADD_LINK}</li>
				<li>{ADD_SITE_LINK}</li>
				<li class="divider"></li>
				<li>{CLIP}</li>
			</ul>
				</div>
				<div class="modal-footer">
					 
				</div>
			</div>		
    <!-- END menu_admin -->
	<ul id="sort-{MENU_ID}" class="nav menu-links">
	  {LINKS}
	</ul>
	<div class="align-center">
		<!-- BEGIN pin -->
		{PIN_PAGE}<br />
		<!-- END pin -->
		{ADMIN_LINK}
	</div>
</div>
