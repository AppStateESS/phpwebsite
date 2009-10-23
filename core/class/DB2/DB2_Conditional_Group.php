<?php
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
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package DB2
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

class DB2_Conditional_Group {
    /**
     * Location of the conditional group in the db object's conditional_group_stack
     * @access private
     * @var integer
     */
    private $position = 0;

    /**
     * Reference to the db object that constructed this object
     * @access private
     * @var object
     */
    private $db2 = null;

    /**
     * @access private
     * @var array
     */
    private $slot = null;

    /**
     * Conjunction to other conditional elements
     * @access private
     * @var string
     */
    private $conjunction = 'AND';

    public function __construct(DB2 $db2, $position, $args)
    {
        $this->position = $position;
        $this->db2 = $db2;
        foreach ($args as $conditional) {
            // If "$conditional" is a conditional group object, remove its position from
            // the database object to prevent repeats
            if (is_a($conditional, 'DB2_Conditional_Group')) {
                $this->db2->dropWhereGroup($conditional->position);
            } elseif (is_a($conditional, 'DB2_Conditional')) {
                // If this is a conditional object, remove it from the
                // general db conditional stack on the table object
                $conditional->dropFromTableStack();
            } else {
                throw new PEAR_Exception(dgettext('core', 'Invalid parameter sent to DB_Conditional_Group constructor.'));
            }
            $this->slot[] = $conditional;
        }
        $this->slot = $args;
    }

    /**
     * Breaks up the conditional group and creates the conditional query. If use_conjunction is
     * true, the conjunction is prefixed to the output.
     *
     * @param boolean use_conjunction : Indicates whether a conjunction should be used
     */
    public function query($use_conjunction=true, $recursion=false)
    {
        $conj = false;

        foreach ($this->slot as $wobj) {
            if (is_a($wobj, 'DB2_Conditional_Group')) {
                $conditional[] = $wobj->query($conj, true);
            } else {
                if (!$conj) {
                    $wobj->disableConjunction();
                }
                $conditional[] = $wobj;
            }
            $conj = true;
        }

        if ($use_conjunction && !$recursion) {
            return $this->getConjunction() . ' (' . implode(' ', $conditional) . ')';
        } else {
            return '(' . implode(' ', $conditional) . ')';
        }
    }

    public function setConjunction($conj)
    {
        $conj = strtoupper(trim($conj));
        if ($conj != 'AND' && $conj != 'OR') {
            throw new PEAR_Exception(dgettext('core', 'Conjunction must be either "AND" or "OR".'));
        }
        $this->conjunction = $conj;
    }

    public function getConjunction()
    {
        return $this->conjunction;
    }
}
?>