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
        <div class="navbar navbar-inverse navbar-fixed-top">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="#">Bootstrap theme</a>
                </div>
                <div class="navbar-collapse collapse pull-right">
                    <ul class="nav navbar-nav">
                        <!-- BEGIN miniadmin -->
                        <li class="dropdown">
                            {MINIADMIN_MINI_ADMIN}
                        </li>
                        <!-- END miniadmin -->
                        <li class="dropdown">
                            {USERS_LOGIN_BOX}
                        </li>
                    </ul>
                </div><!--/.nav-collapse -->
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
        <div id="modal-storage"></div>
        <footer>
            {LAYOUT_FOOTER}
        </footer>
        {JAVASCRIPT}
    </body>
</html>
