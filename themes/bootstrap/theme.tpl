<!DOCTYPE html>
<html lang="en">
    <head>
        {BASE}
        <title>{PAGE_TITLE}</title>
        {METATAGS}
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <link href="{THEME_HTTP}css/bootstrap.min.css" rel="stylesheet" media="screen">
        <link href="{THEME_HTTP}css/local.css" rel="stylesheet" media="screen">
        <link rel="stylesheet" href="{THEME_HTTP}font-awesome/css/font-awesome.min.css">
        {STYLE}
    </head>
    <body id="bootstrap-theme">
        <div class="navbar navbar-inverse">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                        <span class="fa fa-bar"></span>
                        <span class="fa fa-bar"></span>
                        <span class="fa fa-bar"></span>
                    </button>
                    <a class="navbar-brand" href="#">{SITE_TITLE}</a>
                </div>
                <div class="navbar-collapse collapse">
                    {SEARCH_SEARCH_BOX}
                    <!-- BEGIN dropdown -->
                    <ul class="nav navbar-nav navbar-right">
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Page Settings <b class="caret"></b></a>
                                {MINIADMIN_MINI_ADMIN}
                        </li>
                        {USERS_LOGIN_BOX}
                    </ul>
                    <!-- END dropdown -->
                </div>
            </div>
        </div>
        <div id="hero">
            <div class="row">
                <div class="col-md-3">
                    {DEFAULT}
                    {LIKEBOX_DEFAULT}
                </div>
                <div class="col-md-9">
                    {LAYOUT_HEADER}
                    {BODY}
                </div>
            </div>
        </div>
        <hr>
        <footer>
            {LAYOUT_FOOTER}
        </footer>
        {JAVASCRIPT}
    </body>
</html>
