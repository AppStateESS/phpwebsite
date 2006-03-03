{XML}{XML_STYLE}{DOCTYPE}{XHTML}
<head>
{BASE}
<title>{PAGE_TITLE}</title>
{METATAGS}
{JAVASCRIPT}
{STYLE}
<style type="text/css"> @import url("themes/default/style.css"); </style>
<link rel="stylesheet" href="themes/default/default.css" type="text/css" />
<link rel="alternate stylesheet" title="Blue" href="themes/default/blue.css" type="text/css" />

</head>
<body>
<div id="container">
   <div id="top-menu">{SEARCH_SEARCH_BOX}{USERS_LOGIN_BOX}</div>
   <div id="sidepanel">
     {MENU_MENU_1}
     {DEFAULT}
     <div id="notes-list">{NOTES_REMINDER}</div>
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
