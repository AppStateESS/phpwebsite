<?php
/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

abstract class Content extends Resource {
    /**
     * If true, Content is set to be published (seen)
     * @var boolean
     */
    protected $publish = false;

    /**
     * Date after which the content may be publically viewed
     * @var integer
     */
    protected $show_after = 0;

    /**
     * Date after which the content should not be shown publically.
     * @var integer
     */
    protected $hide_after = 0;
}
?>