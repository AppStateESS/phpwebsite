<!doctype html>
<html lang="en">

  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"> {BASE}
    <link href="https://fonts.googleapis.com/css?family=Roboto+Condensed" rel="stylesheet">
    <link href="{THEME_HTTP}dist/css/custom.css" rel="stylesheet" media="screen"> {METATAGS} {STYLE}
    <title>{PAGE_TITLE}</title>
  </head>

  <body>
    <header id="theme-top">
      <div class="header-bar p-1">
        <div class="container">
          <div class="site-title"><a href="./"><h1>{SITE_NAME}</h1></a></div>
          <div class="site-admin">{USERS_LOGIN_BOX}</div>
          <div class="miniadmin">{MINIADMIN_MINI_ADMIN}</div>
          <div id="search-button">
            <i class="fas fa-search"></i>
          </div>
        </div>
      </div>
    </header>
    <div id="sticky-container" class="container"></div>
    <div id="search-menu">
      <div class="close-container">
        <button id="close-search" class="btn btn-outline-dark">
          <i class="fa fa-times"></i></button>
      </div>
      <div class="search-box">
        <form id="search-form">
          <div class="site-search">
            <i class="fas fa-search fa-lg"></i>
            <input id="search-input" placeholder="Search" type="text" />
          </div>
          <div class="search-type">
            <div class="form-check">
              <input id="site-type-1" class="form-check-input" type="radio" name="searchType" value="site" checked="checked"
              />
              <label class="form-check-label" for="site-type-1">This site</label>
            </div>
          </div>
          <div class="submit">
            <button class="btn btn-outline-dark">Search</button>
          </div>
        </form>
      </div>
    </div>
    <div id="main-theme-content" class="container">
      <div id="menu-dropdowns"></div>
      <div id="carousel-container">{CAROUSEL_SLIDES}</div>
      <div id="title-menu">
        <!-- BEGIN menu-view -->
        <div id="top-menu">{MENU_TOP_VIEW}</div>
        <!-- END menu-view -->
      </div>
      <div class="row">
        <div class="col-md-8 col-lg-9 left-side">
          {BODY}
        </div>
        <div class="col-md-4 col-lg-3 right-side">
          <!-- BEGIN side-menu --><div class="side-menu">{MENU_SIDE}</div><!-- END side-menu -->
          <!-- BEGIN default-container --><div class="default-container">{DEFAULT}</div><!-- END default-container -->
        </div>
      </div>
    </div>
    <div id="hidden-valley">{HIDDEN_VALLEY}</div>
    {ANALYTICS_END_BODY}
    <script src="{THEME_HTTP}dist/js/custom.js"></script>
    {JAVASCRIPT}
  </body>

</html>
