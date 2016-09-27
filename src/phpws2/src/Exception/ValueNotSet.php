<?php
namespace phpws2\Exception;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ValueNotSet
 *
 * @author matt
 */
class ValueNotSet extends \Exception
{
    public function __construct()
    {
        parent::__construct('Value not set', 1);
    }
}
