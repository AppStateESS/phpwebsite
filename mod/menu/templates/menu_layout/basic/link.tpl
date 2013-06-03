<li id="{ID}" class="menu-link link-level-{LEVEL} {CURRENT_LINK}">

  <!-- BEGIN admin_links -->
        <div class="btn-group pull-right">
            <a class="btn dropdown-toggle btn-mini" data-toggle="dropdown" href="#"> <i class="icon-cog">
              </i><span class="caret"></span>
            </a>
            <ul class="dropdown-menu">
                <li>{EDIT_LINK}</li>
                <li>{DELETE_LINK}</li>
                <li>{LINK_INDENT}</li>
                <li>{LINK_OUTDENT}</li>
                <li>{PIN_LINK}</li>
                <li>{ADD_LINK}</li>
                <li>{ADD_SITE_LINK}</li>
                <li>{MOVE_LINK_UP}</li>
                <li>{MOVE_LINK_DOWN}</li>
            </ul>
        </div>
    <!-- END admin_links -->
  
  <a
    href="{LINK_URL}"
    class="{ACTIVE}
    <!--  BEGIN dropdown -->
    dropdown-toggle" data-toggle="{LINK_DROPDOWN}
    <!--  END dropdown -->
    ">{LINK_TEXT}</a>
    
      <!-- BEGIN sublink -->
      <div class="well">
      <ul>
        {SUBLINK}
      </ul>
      </div>
  <!-- END sublink -->
</li>
