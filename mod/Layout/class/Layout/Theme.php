<?php
namespace Layout;
/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Theme {
    private $current;

    public function __construct()
    {
        $this->current = Settings::get('layout', 'theme');

        $db = Database::newDB();
        $db->addTable('layout_box')->addWhere('theme', $this->current);

    }
}

?>
