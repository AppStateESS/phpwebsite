
<li id="{ID}" class="menu-link link-level-{LEVEL} {CURRENT_LINK}">
<!-- BEGIN add-link --> <span class="menu-admin"> {ADMIN} <!-- BEGIN admin-links -->
<span class="menu-link-pop">{EDIT_LINK} {DELETE_LINK} {LINK_INDENT} {LINK_OUTDENT}
{PIN_LINK} {ADD_LINK} {ADD_SITE_LINK}
{MOVE_LINK_UP} {MOVE_LINK_DOWN} </span> <!-- END admin-links --> </span> <!-- END add-link -->
{LINK}<!-- BEGIN sublink -->
<ul id="{PARENT_ID}"  class="menu-sub-{LEVEL}">
    {SUBLINK}
</ul>
<!-- END sublink --></li>
