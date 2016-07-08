<?php

class Variable extends \phpws2\Variable
{
    // Required because Variable abstract
    protected function verifyValue($value)
    {
        return parent::verifyValue($value);
    }

}
