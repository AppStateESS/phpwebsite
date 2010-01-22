<?php

/**
 * Main Controller Class for Link module
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('link', 'LinkContext.php');
PHPWS_Core::initModClass('link', 'LinkClassLoader.php');
PHPWS_Core::initModClass('link', 'LinkCommandFactory.php');
PHPWS_Core::initModClass('link', 'LinkExceptionFactory.php');
PHPWS_Core::initModClass('link', 'LinkCommand.php');
PHPWS_Core::initModClass('link', 'LinkFactory.php');

class LinkController
{
    private static $INSTANCE;

    var $content;

    private function __construct()
    {
        $this->context = new LinkContext();
    }

    public static function getInstance()
    {
        if(is_null(self::$INSTANCE)) {
            self::$INSTANCE = new LinkController();
        }

        return self::$INSTANCE;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function process()
    {
        $cmd = LinkCommandFactory::getCommand($this->context->get('action'));

        $cmd->execute($this->context);
    }

    public function unregisterModule($module)
    {
        // TODO: Implement
    }

    public static function showLinksForKey($key)
    {
        $links = LinkFactory::getByKey($key);
        PHPWS_Core::initModClass('link', 'LinkView.php');

        $content = '';
        if(!empty($links)) {
            foreach($links as $link) {
                $view = new LinkView($link);
                $content .= $view->show(new LinkContext());
            }
        }

        $class = LinkClassLoader::staticInit('CreateLinkView');
        $view = new $class($key);
        $content .= $view->show(new LinkContext());

        return $content;
    }
}

?>
