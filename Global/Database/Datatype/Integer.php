<?php

namespace Database\Datatype;

/*
 * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 *
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

class Integer extends \Database\Datatype {

    /**
     * Loads an integer variable into the default parameter.
     */
    protected function loadDefault()
    {
        $this->default = new \Variable\Integer(null, 'default');
        $this->default->allowNull(true);
    }

    /**
     * Checks some conditionals for the default value.
     * @return string
     */
    public function getDefault()
    {
        if (is_null($this->default)) {
            return null;
        }
        if ($this->default->IsNull() && !$this->is_null) {
            return 'default 0';
        }
        return 'default ' . $this->default;
    }

}

?>
