<?php
/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

class Site extends \Resource {
    /**
     * Site objects live in the sites table
     * @var string
     */
    protected $table = 'sites';
    protected $name = null;

    public static function getCurrentSite()
    {
        return null;
    }

}
?>
