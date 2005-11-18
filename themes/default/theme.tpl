{XML}{XML_STYLE}{DOCTYPE}{XHTML}
<head>
{BASE}
<title>{PAGE_TITLE}</title>
{METATAGS}
{JAVASCRIPT}
{STYLE}
</head>
<body>
<div id="container">
   <!-- BEGIN top_menu --><div id="top-menu">{SEARCH_SEARCH_BOX}
   {USERS_LOGIN_BOX}<div style="clear:both"></div></div><!-- END top_menu -->
   <div class="category-menu-bar">{CATEGORIES_ADMIN_MENU}</div>
   <div id="sidepanel">
     {DEFAULT}
     <div id="notes-list">{NOTES_REMINDER}</div>
   </div>
   <div id="gutter"></div>
   <div id="main-content">
     <!-- BEGIN header --><div id="header">{LAYOUT_HEADER}</div><!-- END header -->
     {BODY}
     <div id="bottom">{BOTTOM}</div>
   </div>
   <div id="footer">{LAYOUT_FOOTER}</div>
</div>
</body>
</html>
