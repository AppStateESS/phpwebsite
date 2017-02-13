<?php

namespace phpws2\Variable;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package phpws2
 * @subpackage Variable
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class DateTime extends DateVar {

    /**
     * Form input should be of datetime type
     * @var string
     */
    protected $input_type = 'datetime';
    /**
     * Of this format: YYYY-MM-DD HH:MM:SS
     * @var string
     */
    protected $format = '%Y-%m-%d %H:%M:%S';

}
