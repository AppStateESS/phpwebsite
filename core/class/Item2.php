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
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

Core::initCoreClass('DB2.php');

abstract class Item2 extends DB2_Object {
    protected $id = 0;
    protected $key_id = 0;
    protected $title = null;
    protected $created = 0;
    protected $updated = 0;
    protected $creater_id = 0;
    protected $updater_id = 0;
    protected $ip = null;
    protected $active = true;

    public function __construct($id=0)
    {
        $this->setPrimaryKeyColumn('id');
        if ($id) {
            $this->setId($id);
            $this->load();
        }
    }


    public function setId($id)
    {
        $this->id = (int)$id;
    }

    protected function pushValues(array $values)
    {
        foreach ($values as $key=>$val) {
            $this->$key = $val;
        }
    }

    protected function pullValues()
    {
        return get_object_vars($this);
    }

    public function setTitle($title, $allow_tags=null)
    {
        $this->title = strip_tags($title, $allow_tags);
    }

    public function getTitle()
    {
        return $this->title;
    }

    /**
     * May do something here for artifical deletes
     */
    public function delete()
    {
        self::delete();
    }
}
?>