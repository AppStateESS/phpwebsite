<li id="{ID}" class="dropdown menu-link link-level-{LEVEL} {CURRENT_LINK}">

  <!-- BEGIN admin_links -->
        <div class="btn-group pull-right">
		<a id="modal-773796" href="#menu{ID}" role="button" class="btn" data-toggle="modal"><i class="icon-cog">
		</i><span class="caret"></span></a>
		</div>	
<div id="menu{ID}" class="modal hide fade" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<div class="modal-header">
					 <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h3 id="myModalLabel">
						Menu Item Options
					</h3>
				</div>
				<div class="modal-body">
					<ul>
				<li>{ADD_LINK}</li>
                <li>{ADD_SITE_LINK}</li>
                <li>{EDIT_LINK}</li>
                <li>{DELETE_LINK}</li>
                <li>{LINK_INDENT}</li>
                <li>{LINK_OUTDENT}</li>
                <li>{PIN_LINK}</li>
                <li>{MOVE_LINK_UP}</li>
                <li>{MOVE_LINK_DOWN}</li>
            </ul>
				</div>
				<div class="modal-footer">
				</div>
			</div>			
    <!-- END admin_links -->
  
  <a href="{LINK_URL}" class="{ACTIVE}<!--  BEGIN dropdown -->{DROPDOWN_TOGGLE}" data-toggle="{LINK_DROPDOWN}<!--  END dropdown -->">{LINK_TEXT}</a>
      <!-- BEGIN sublink -->
      <ul class="dropdown-menu">
        {SUBLINK}
      </ul>
  <!-- END sublink -->
</li>
