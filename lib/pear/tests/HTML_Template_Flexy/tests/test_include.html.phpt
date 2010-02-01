--TEST--
Template Test: include.html
--FILE--
<?php
require_once 'testsuite.php';
require_once 'HTML/Template/Flexy/Factory.php';
 

compilefile('include.html',
    array(
	    'range' => range('a', 'z'),
	    'foo'   => 'bar',
    )
    
);

--EXPECTF--
===Compiling include.html===



===Compiled file: include.html===
<html>
	<body>

    the variable is <?php echo htmlspecialchars($t->foo);?>

    <table>
        <?php if ($this->options['strict'] || (is_array($t->range)  || is_object($t->range))) foreach($t->range as $key => $value) {?><tr>
            <?php 
$x = new HTML_Template_Flexy($this->options);
$x->compile('include_block.html');
$_t = function_exists('clone') ? clone($t) : $t;
foreach(get_defined_vars()  as $k=>$v) {
    if ($k != 't') { $_t->$k = $v; }
}
$x->outputObject($_t, $this->elements);
?>
        </tr><?php }?>
    </table>

	</body>
</html>


===With data file: include.html===
<html>
	<body>

    the variable is bar
    <table>
        <tr>
                <td width="60">0</td>
    <td><a href="#foo">a</a></td>        </tr><tr>
                <td width="60">1</td>
    <td><a href="#foo">b</a></td>        </tr><tr>
                <td width="60">2</td>
    <td><a href="#foo">c</a></td>        </tr><tr>
                <td width="60">3</td>
    <td><a href="#foo">d</a></td>        </tr><tr>
                <td width="60">4</td>
    <td><a href="#foo">e</a></td>        </tr><tr>
                <td width="60">5</td>
    <td><a href="#foo">f</a></td>        </tr><tr>
                <td width="60">6</td>
    <td><a href="#foo">g</a></td>        </tr><tr>
                <td width="60">7</td>
    <td><a href="#foo">h</a></td>        </tr><tr>
                <td width="60">8</td>
    <td><a href="#foo">i</a></td>        </tr><tr>
                <td width="60">9</td>
    <td><a href="#foo">j</a></td>        </tr><tr>
                <td width="60">10</td>
    <td><a href="#foo">k</a></td>        </tr><tr>
                <td width="60">11</td>
    <td><a href="#foo">l</a></td>        </tr><tr>
                <td width="60">12</td>
    <td><a href="#foo">m</a></td>        </tr><tr>
                <td width="60">13</td>
    <td><a href="#foo">n</a></td>        </tr><tr>
                <td width="60">14</td>
    <td><a href="#foo">o</a></td>        </tr><tr>
                <td width="60">15</td>
    <td><a href="#foo">p</a></td>        </tr><tr>
                <td width="60">16</td>
    <td><a href="#foo">q</a></td>        </tr><tr>
                <td width="60">17</td>
    <td><a href="#foo">r</a></td>        </tr><tr>
                <td width="60">18</td>
    <td><a href="#foo">s</a></td>        </tr><tr>
                <td width="60">19</td>
    <td><a href="#foo">t</a></td>        </tr><tr>
                <td width="60">20</td>
    <td><a href="#foo">u</a></td>        </tr><tr>
                <td width="60">21</td>
    <td><a href="#foo">v</a></td>        </tr><tr>
                <td width="60">22</td>
    <td><a href="#foo">w</a></td>        </tr><tr>
                <td width="60">23</td>
    <td><a href="#foo">x</a></td>        </tr><tr>
                <td width="60">24</td>
    <td><a href="#foo">y</a></td>        </tr><tr>
                <td width="60">25</td>
    <td><a href="#foo">z</a></td>        </tr>    </table>

	</body>
</html>