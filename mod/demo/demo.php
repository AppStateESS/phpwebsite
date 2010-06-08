<?php
namespace demo;

/**
 * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package
 * @license http://opensource.org/licenses/gpl-3.0.html
 */
class Module extends \core\Module2 {
    protected $title = 'demo';
    protected $proper_name = '2.0 module demonstration';
    protected $description = 'This is a demonstration of the 2.0 module class.';

    public function home()
    {
        echo 'HI! Welcome to the demo! I am in home.';
    }

    public function start(){

    }

    public function end(){

    }
}


?>