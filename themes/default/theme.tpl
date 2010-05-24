{XML}{XML_STYLE}{DOCTYPE}{XHTML}
<head>
{BASE}
<title>{PAGE_TITLE}</title>
{METATAGS}
{JAVASCRIPT}
{STYLE}
<!--[if lt IE 7]>
  <link rel="stylesheet" type="text/css" href="{THEME_HTTP}themes/default/hacks.css" />
<![endif]-->
<!--[if IE 7]>
  <link rel="stylesheet" type="text/css" href="{THEME_HTTP}themes/default/ie7.css" />
<![endif]-->

</head>
<body>
{SAMPLE}
{LAYOUT_HEADER}
<div id="container">
   <div id="top-menu">{SEARCH_SEARCH_BOX}{USERS_LOGIN_BOX}<hr />{BREADCRUMB_VIEW}
   {TOP_MENU}
   </div>
   <div id="sidepanel">
     {MENU_MENU_1}
     {DEFAULT}
     <!-- BEGIN notes --><div id="notes-list">{NOTES_REMINDER}</div><!-- END notes -->
   </div>
   <div id="main-content">
     <!-- BEGIN header --><div id="header">{LAYOUT_HEADER}</div><!-- END header -->
     {BODY}
     <div id="bottom">{BOTTOM}</div>
   </div>
   <div id="footer">{LAYOUT_FOOTER}</div>
</div>
</body>
</html>
