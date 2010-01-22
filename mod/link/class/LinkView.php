<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class LinkView // extends View
{
    var $link;

    public function __construct(Link $link)
    {
        $this->link = $link;
    }

    public function show($context)
    {
        Layout::addStyle('link', 'style.css');
        $link = $this->link;

        $tpl = array();
        $tpl['HREF'] = $link->getHref();
        $tpl['TITLE'] = $link->getTitle();
        $tpl['OTHER'] = $link->getOther();

        // TODO: Ratings

        return PHPWS_Template::process($tpl, 'link', 'LinkView.tpl');
    }
}

?>
