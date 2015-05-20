<!DOCTYPE html>
<html lang="en">
    <head>
        {BASE}
        <title>{PAGE_TITLE}</title>
        {METATAGS}
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <link rel="apple-touch-icon-precomposed" sizes="144x144" href="{THEME_HTTP}img/apple-touch-icon-144x144-precomposed.png">
        <link rel="apple-touch-icon-precomposed" sizes="114x114" href="{THEME_HTTP}img/apple-touch-icon-114x114-precomposed.png">
        <link rel="apple-touch-icon-precomposed" sizes="72x72" href="{THEME_HTTP}img/apple-touch-icon-72x72-precomposed.png">
        <link rel="apple-touch-icon-precomposed" sizes="57x57" href="{THEME_HTTP}img/apple-touch-icon-57x57-precomposed.png">
        <link rel="shortcut icon" type="image/x-icon" href="{THEME_HTTP}img/favicon.ico">
        <link href="{THEME_HTTP}css/bootstrap.min.css" rel="stylesheet" media="screen">
        <link href="{THEME_HTTP}css/local.css" rel="stylesheet" media="screen">
        <link href="{THEME_HTTP}css/header.css" rel="stylesheet" media="screen">
        <link rel="stylesheet" href="{THEME_HTTP}font-awesome/css/font-awesome.min.css">
        <script src="{THEME_HTTP}js/bootstrap.min.js"></script>
        {STYLE}
    </head>
    <body id="bootstrap-theme">
        <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="./">Bootstrap theme</a>
                </div>
                <div class="collapse navbar-collapse navbar-right">
                    <ul class="nav navbar-nav">
                        <!-- BEGIN miniadmin -->
                        <li class="dropdown">
                            {MINIADMIN_MINI_ADMIN}
                        </li>
                        <!-- END miniadmin -->
                        <li class="dropdown">
                            {USERS_LOGIN_BOX}
                        </li>
                        <li>{SEARCH_SEARCH_BOX}</li>
                    </ul>
                </div><!--/.nav-collapse -->
            </div>
        </div>
        <div>{MENU_TOP_VIEW}</div>
        {CAROUSEL_SLIDES}
        <div id="hero">
            <div class="row">
                <div class="col-md-3 col-sm-3">
                    {DEFAULT}
                    {LIKEBOX_DEFAULT}
                </div>
                <div class="col-md-9 col-sm-9">
                    {BODY}
                </div>
            </div>
        </div>
        <hr>
        {JAVASCRIPT}
        <footer>
        </footer>
        {ANALYTICS_END_BODY}
    </body>
</html>
