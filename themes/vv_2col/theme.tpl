{XML}{XML_STYLE}{DOCTYPE}{XHTML}
<head>
{BASE}
<title>{PAGE_TITLE}</title>
<meta http-equiv="imagetoolbar" content="no" />
{METATAGS}
{JAVASCRIPT}
{STYLE}
<!--[if lt IE 7]>
  <link rel="stylesheet" type="text/css" href="themes/vv_2col/hacks.css" />
<![endif]-->
<!--[if IE 7]>
  <link rel="stylesheet" type="text/css" href="themes/vv_2col/ie7.css" />
<![endif]-->

</head>
<body id="mainpage">

<!-- Mast start -->
<div id="mast">
    <div id="banners">{BANNERS}</div>
    <a href="./"><img src="themes/vv_2col/img/logo.gif" alt="Logo Mast Image" width="500" height="100" border="0" /></a>
</div>
<!-- Mast end -->

<!-- BEGIN layout_header --><div id="header"><div class="padding">{LAYOUT_HEADER}</div></div><!-- END layout_header -->
<div id="top-menu">
    <div class="padding">
        {USERS_LOGIN_BOX}
        <!-- BEGIN breadcrumb_view --><hr />{BREADCRUMB_VIEW}<!-- END breadcrumb_view -->
        <!-- BEGIN top_menu -->{TOP_MENU}<!-- END top_menu -->
    </div>
</div>

<!-- Main content start -->
<div class="colmask leftmenu">
	<div class="colleft">
		<div class="col1">
			<!-- Column 1 (main) start -->
            {BODY}
            <div id="bottom">{BOTTOM}</div>
			<!-- Column 1 end -->
		</div>
		<div class="col2">
			<!-- Column 2 (side) start -->
            {MENU_MENU_1}
            {DEFAULT}
            <!-- BEGIN notes --><div id="notes-list">{NOTES_REMINDER}</div><!-- END notes -->
            {SEARCH_SEARCH_BOX}
			<!-- Column 2 end -->
		</div>
	</div>
</div>
<!-- Main content end -->

<div id="footer">
    <div class="padding">
        <!-- BEGIN layout_footer -->{LAYOUT_FOOTER}<!-- END layout_footer -->
        <p>&copy;{YEAR} {HOST}</p>
    </div>
</div>

</body>
</html>
