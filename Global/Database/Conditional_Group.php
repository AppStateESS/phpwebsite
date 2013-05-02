<?php

namespace Database;

/**
 * Creates an object grouping a stack of conditional objects.
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage DB
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Conditional_Group {

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
    private $DB = null;
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

    /**
     *
     * @param DB $DB
     * @param <type> $position
     * @param <type> $args
     */
    public function __construct(DB $DB, $position, $args)
    {
        $this->position = $position;
        $this->DB = $DB;
        foreach ($args as $conditional) {
            // If "$conditional" is a conditional group object, remove its position from
            // the database object to prevent repeats
            if ($conditional instanceof Conditional_Group) {
                $this->DB->dropWhereGroup($conditional->position);
            } elseif ($conditional instanceof Conditional) {
                // If this is a conditional object, remove it from the
                // general db conditional stack on the table object
                $conditional->dropFromTableStack();
            } else {
                throw new \Exception(t('Invalid parameter sent to Conditional_Group constructor.'));
            }
            $this->slot[] = $conditional;
        }
        $this->slot = $args;
    }

    /**
     * Breaks up the conditional group and creates the conditional query. If use_conjunction is
     * true, the conjunction is prefixed to the output.
     *
     * @todo Formally this method has a recursion parameter. When recalled by itself,
     * it prevented a conjunction from being prefixed to a condition group. This
     * code didn't work so it was removed. Not sure why I added it originally now,
     * but it is worth noting for later testing.
     * @param boolean use_conjunction : Indicates whether a conjunction should be used
     */
    public function query($use_conjunction=true)
    {
        $conj = false;
        $conditional = array();

        foreach ($this->slot as $wobj) {
            if ($wobj instanceof Conditional_Group) {
                $conditional[] = $wobj->query($conj);
            } else {
                if (!$conj) {
                    $wobj->disableConjunction();
                }
                $conditional[] = $wobj;
            }
            $conj = true;
        }

        if ($use_conjunction) {
            return $this->getConjunction() . ' (' . implode(' ', $conditional) . ')';
        } else {
            return '(' . implode(' ', $conditional) . ')';
        }
    }

    /**
     * Sets the conjunction to AND or OR.
     * @param string $conj
     */
    public function setConjunction($conj)
    {
        $conj = strtoupper(trim($conj));
        if ($conj != 'AND' && $conj != 'OR') {
            throw new \Exception(t('Conjunction must be either "AND" or "OR".'));
        }
        $this->conjunction = $conj;
    }

    /**
     *
     * @return string
     */
    public function getConjunction()
    {
        return $this->conjunction;
    }

}

?>