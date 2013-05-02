<?php

namespace Variable;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage Variable
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Date extends Integer {

    protected $input_type = 'date';
    /**
     * Output format. Based on strftime.
     * @var string
     */
    protected $format = '%F';

    public function getInput()
    {
        $input = parent::getInput();
        $input->setSize(10, 10);
        return $input;
    }

    public function addTime($time)
    {
        $this->set($this->value + $time);
    }

    /**
     * Sets the output format.
     *
     * @see http://php.net/manual/en/function.strftime.php
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    public function toDatabase()
    {
        return $this->value;
    }

    public function __toString()
    {
        return strftime($this->format, $this->value);
    }

    public function stamp()
    {
        $this->set(time());
    }

}

?>