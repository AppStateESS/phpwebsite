<!DOCTYPE html>
<html>
  <head>
    {BASE}
    <title>{PAGE_TITLE}</title>
    {METATAGS}
    {JAVASCRIPT}
    {STYLE}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="{THEME_HTTP}css/local.css" rel="stylesheet" media="screen">
    <link rel="apple-touch-icon-precomposed" sizes="144x144"
        href="{THEME_HTTP}favicon/apple-touch-icon-144x144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114"
        href="{THEME_HTTP}favicon/apple-touch-icon-114x114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72"
        href="{THEME_HTTP}favicon/apple-touch-icon-72x72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="57x57"
        href="{THEME_HTTP}favicon/apple-touch-icon-57x57-precomposed.png">
    <link rel="shortcut icon" type="image/x-icon"
        href="{THEME_HTTP}favicon/favicon.ico">
    <script src="{THEME_HTTP}js/bootstrap.min.js"></script>
  </head>
  <body>
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">
          <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="brand" href="index.php">{SITE_TITLE}</a>
          <div class="nav-collapse collapse">
            <p class="navbar-text pull-right">
              Herp Derpinson
            </p>
            <ul class="nav">
            </ul>
          </div>
        </div>
      </div>
    </div>
    <div class="container-fluid">
      <div class="row-fluid">
        <div class="span3">
          {DEFAULT}
        </div>
        <div class="span9">
          {LAYOUT_HEADER}
          {BODY}
        </div>
      </div>
    </div>
    <hr>
    <footer>
      {LAYOUT_FOOTER}
    </footer>
    <script data-main="js/main" src="js/libs/require/require.js"></script>
  </body>
</html>
