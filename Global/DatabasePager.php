<?php

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class DatabasePager extends Pager {
    /**
     *
     * @var \Database\DB
     */
    protected $db;

    public function __construct(\Database\DB $db)
    {
        $this->db = $db;
    }

    

}

?>
