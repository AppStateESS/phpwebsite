<?php
namespace core;
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
 * Module2 is an extendable class forming the foundation of 2.0 modules.
 *
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

abstract class Module2 extends Data {
    protected $title = null;
    protected $proper_name = null;
    protected $description = null;
    protected $version = null;
    protected $host_url = null;
    protected $update_url = null;
    protected $dependencies = null;
    protected $tables = null;

    /**
     * The home function defines what the module does when it is called from
     * the url. For example, if your module was called Foo and the address was
     * http://yoursite.com/foo/
     * then home decides what to do next. This function takes the place of the
     * old index.php in 1.7.0 and below.
     */
    abstract public function home();

    abstract public function start();
    abstract public function end();

    public function info()
    {
        $template = $this->extract();
        echo Template::process($template, 'core', 'module_info.html');
    }

}

?>