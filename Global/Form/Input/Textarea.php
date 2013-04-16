<?php

namespace Form\Input;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage Form
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Textarea extends \Form\Base {

    /**
     * @var string
     */
    protected $tag_type = 'textarea';
    /**
     * Unlike other inputs, textarea is an open tag
     * @var boolean
     */
    protected $open = true;

}

?>