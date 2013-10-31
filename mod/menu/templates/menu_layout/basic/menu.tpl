<div class="menu" id="{MENU_ID}">
    <div>
        <!-- BEGIN menu_admin -->
        <div class="btn-group">
            <button class="btn dropdown-toggle btn-default btn-sm pull-right" data-toggle="dropdown"> <i class="fa fa-list">
                </i> Menu Options <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <!-- BEGIN hide -->
                <!-- BEGIN pin-page --><li>{PIN_PAGE}</li><!-- END pin-page -->
                <!-- BEGIN pin-link --><li>{PIN_LINK}</li><!-- END pin-link -->
                <!-- BEGIN add-link --><li>{ADD_LINK}</li><!-- END add-link -->
                <!-- BEGIN add-site-link --><li>{ADD_SITE_LINK}</li><!-- END add-site-link -->
                <!-- BEGIN clip --><li>{CLIP}</li>
                <!-- END hide -->
                <li>{ADMIN_LINK}</li>
            </ul>
        </div>
        <!-- END menu_admin -->
        <h5 class="menu-title">{TITLE}</h5>
        <div class="clearfix"></div>
    </div>
    <ul id="sort-{MENU_ID}" class="nav nav-pills nav-stacked menu-links">
        {LINKS}
    </ul>
</div>