<?php

/**
 * debugger for the phpWebSite core
 *
 * @author Steven Levin <steven@NOSPAM.tux.appstate.edu>
 * @version $Id$
 * @package Core
 */

class PHPWS_Debug {

    function test($value)
    {
        if (empty($value))
            return 'NULL';
        switch(gettype($value)){
        case 'object':
            return PHPWS_Debug::testObject($value);
            break;
      
        case 'array':
            return 'Array' . PHPWS_Debug::testArray($value);
            break;

        case 'boolean':
            if ($value)
                return 'TRUE';
            else
                return 'FALSE';

        case 'string':
            return preg_replace("/\n/", "\\\\n", htmlspecialchars($value)) . '<br />';
            break;

        default:
            return $value;
        }
    }

    function request()
    {
        return PHPWS_Debug::test($_REQUEST);
    }

    function post()
    {
        return PHPWS_Debug::test($_POST);
    }

    function get()
    {
        return PHPWS_Debug::test($_GET);
    }
  
    function server()
    {
        return PHPWS_Debug::test($_SERVER);
    }

    function env()
    {
        return PHPWS_Debug::test($_ENV);
    }

    function cookie()
    {
        return PHPWS_Debug::test($_COOKIE);
    }

    function files()
    {
        return PHPWS_Debug::test($_FILES);
    }

    function sessions()
    {
        return PHPWS_Debug::test(array_keys($_SESSION));
    }

    /**
     * testObject
     *
     * Outputs variables set in an object
     *
     * @author Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
     * @modified Steven Levin <steven@NOSPAM.tux.appstate.edu>
     * @param object $objVar object to display
     * @param bool $displayTags whether or not to show html tags
     * @return string table of object variables
     */
    function testObject($objVar, $displayTags=1)
    {
        if(is_object($objVar)) {
            $test_recursion = md5(serialize($objVar));
            
            if (isset($GLOBALS['Test_Recursion'])) {
                if (in_array($test_recursion, $GLOBALS['Test_Recursion'])) {
                    return _('Recursive object:') . ' ' . get_class($objVar);
                }
            }
            
            $GLOBALS['Test_Recursion'][]  = $test_recursion;

            $objectInfo = (get_object_vars($objVar));
            return '<b>' . _('Class Name') . ':</b> ' . get_class($objVar) .
                PHPWS_Debug::testArray($objectInfo, $displayTags);
        }

        if (gettype($objVar) != 'object') {
            return sprintf(_('PHPWS_Debug: testObject received a/an %s variable, not an object.'), gettype($objVar)) . '<br />';
        } else {
            return _('This is an incomplete object. If this is a sessioned object, make sure to declare the class before the variable.') . '<br />';
        }
    } // END FUNC testObject


    /**
     * testArray
     *
     * Returns a table displaying the contents of an array
     *
     * @author Matthew McNaney <matt@NOSPAM.tux.appstate.edu>
     * @modified Steven Levin <steven@NOSPAM.tux.appstate.edu>
     * @param array $arrayVar array to be tested
     * @param bool $displayTags whether or not to show html tags
     * @return string table with contents of array 
     */
    function testArray($arrayVar, $displayTags=1) 
    {
        $test_recursion = md5(serialize($arrayVar));

        if (isset($GLOBALS['Test_Recursion'])) {
            if (in_array($test_recursion, $GLOBALS['Test_Recursion'])) {
                return _('Recursive array');
            }
        }

        $GLOBALS['Test_Recursion'][]  = $test_recursion;

        translate('core');
        if(is_array($arrayVar)) {
            if(count($arrayVar)) {
                $info[] =  
'<table cellpadding="3" border="1">
  <tr>
    <td><b>' . _('KEY') . '</b></td>
    <td><b>' . _('VALUE') . '</b></td>
';

                foreach($arrayVar as $key => $value) {
                    if(is_array($value)) {
                        $value = PHPWS_Debug::testArray($value, $displayTags);
                    } else if(is_object($value)) {
                        $value = PHPWS_Debug::testObject($value, $displayTags);
                    } else if($displayTags && is_string($value)) {
                        $value = htmlspecialchars($value);
                    } else if($value !== NULL) {
                        ob_start();
                        var_dump($value);
                        $value = ob_get_contents();
                        ob_end_clean();
                    } else {
                        $value = 'NULL';
                    }

                    $info[] = '  <tr>'; 
                    $info[] = '    <td valign="top"><b>' . htmlspecialchars($key) . '</b></td>';
                    $info[] = '    <td>' . $value . '</td>';
                    $info[] = '  </tr>';
                }
                $info[] = '</table>';
                return implode("\n", $info);
            } else {
                return _('Array contained no values.');
            }
        } else {
            return 'PHPWS_Debug: testArray received a/an ' . gettype($arrayVar) . ' variable, not an array.<br />';
        }
    } // END FUNC testArray

} // END CLASS PHPWS_Debug

function test($value, $exitAfter=FALSE)
{
    echo PHPWS_Debug::test($value);
    if ($exitAfter) {
        exit();
    }
}

function objectInfo($object)
{
    if (!is_object($object)){
        if (gettype($object) == 'object') {
            echo _('This is an incomplete object. If this is a sessioned object, make sure to declare the class before the variable.') . '<br />';
        } else {
            printf(_('Variable is a %s, not an object.'), gettype($object));
        }
        return;
    }

    $info['class'] = get_class($object);
    $info['methods'] = get_class_methods($info['class']);

    test($info);
    return TRUE;
}

?>
