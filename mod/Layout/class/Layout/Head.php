<?php
namespace Layout;
/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Head {

    private $page_title;
    private $Meta;
    private $link;

    public function __construct()
    {
        $this->Meta = new Meta;
    }

}

?>
