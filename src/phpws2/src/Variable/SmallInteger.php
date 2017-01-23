<?php

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

/**
 * An integer meant for smaller amounts.
 */

namespace phpws2\Variable;

class SmallInteger extends Integer
{

    protected $high_range = 32767;
    protected $column_type = 'Smallint';

    public function setRange($low_range = 0, $high_range = 32767, $increment = 1)
    {
        return parent::setRange($low_range, $high_range, $increment);
    }

}
