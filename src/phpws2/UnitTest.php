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
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

class UnitTest {

    public $errors;
    public $name;
    public $result;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function test()
    {
        $args = func_get_args();
        $para = array();
        foreach ($args as $count => $val) {
            switch ($count) {
                case 0:
                    $obj = $val;
                    break;
                case 1:
                    $method = $val;
                    break;

                default:
                    $para[] = $val;
            }
        }
        echo '<p>';
        try {
            echo $this->name, ': ';
            if (call_user_func_array(array($obj, $method), $para)) {
                echo 'Success!';
            } else {
                echo 'Failure!';
            }
        } catch (\Error $e) {
            echo 'Failure<br>', '<i>' . $e->getMessage() . '</i>';
        }
        echo '</p>';
    }

}

?>
