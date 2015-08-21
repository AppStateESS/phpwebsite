<?php

/**
 * debugger for the phpWebSite core
 *
 * @author Steven Levin
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 * @package Core
 */

define('DEITY_ONLY_TEST', false);

class PHPWS_Debug {
	public static function test($value, $show_recursive=FALSE)
	{
		if ( DEITY_ONLY_TEST && (!isset($_SESSION['User']) || !class_exists('Current_User') || !Current_User::isDeity()) ) {
			return;
		}

		if (empty($value)) {
			$value = PHPWS_Debug::emptyVal($value);
		}

		switch(1) {
			case is_object($value):
				return PHPWS_Debug::testObject($value, 1, $show_recursive);
				break;

			case is_array($value):
				return 'Array' . PHPWS_Debug::testArray($value, 1, $show_recursive);
				break;

			case is_bool($value):
				if ($value) {
					return '<pre>bool(TRUE)</pre>';
				}
				else {
					return '<pre>bool(FALSE)</pre>';
				}

			case is_numeric($value):
				return '<pre>' . $value . '</pre>';

			case is_string($value):
				return '<pre>' . preg_replace('/\n|(\r\n)/', '\n', htmlspecialchars($value)) . '</pre>';
				break;

			default:
				return '<pre>' . $value . '</pre>';
		}
	}

	public static function request()
	{
		return PHPWS_Debug::test($_REQUEST);
	}

	public static function post()
	{
		return PHPWS_Debug::test($_POST);
	}

	public static function get()
	{
		return PHPWS_Debug::test($_GET);
	}

	public static function server()
	{
		return PHPWS_Debug::test($_SERVER);
	}

	public static function env()
	{
		return PHPWS_Debug::test($_ENV);
	}

	public static function cookie()
	{
		return PHPWS_Debug::test($_COOKIE);
	}

	public static function files()
	{
		return PHPWS_Debug::test($_FILES);
	}

	public static function sessions()
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
	public static function testObject($objVar, $displayTags=TRUE, $show_recursive=FALSE)
	{
		if(is_object($objVar)) {
			$test_recursion = md5(serialize($objVar));

			if ($show_recursive && isset($GLOBALS['Test_Recursion'])) {
				if (in_array($test_recursion, $GLOBALS['Test_Recursion'])) {
					return _('Recursive object:') . ' ' . get_class($objVar);
				}
			}

			if (!$show_recursive) {
				$GLOBALS['Test_Recursion'][]  = $test_recursion;
			}

			$objectInfo = (get_object_vars($objVar));
			return '<b>' . _('Class Name') . ':</b> ' . get_class($objVar) .
			PHPWS_Debug::testArray($objectInfo, $displayTags, $show_recursive);
		}

		if (!is_object($objVar)) {
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
	public static function testArray($arrayVar, $displayTags=TRUE, $show_recursive=FALSE)
	{

		if (empty($arrayVar)) {
			return '<pre>' . PHPWS_Debug::emptyVal($arrayVar) . '</pre>';
		}
		$test_recursion = md5(serialize($arrayVar));

		if (!$show_recursive && isset($GLOBALS['Test_Recursion'])) {
			if (in_array($test_recursion, $GLOBALS['Test_Recursion'])) {
				return _('Recursive array');
			}
		}

		if (!$show_recursive) {
			$GLOBALS['Test_Recursion'][]  = $test_recursion;
		}


		if(is_array($arrayVar)) {
			if(!empty($arrayVar)) {
				$info[] =
'<table cellpadding="3" border="1">
  <tr>
    <td><b>' . 'KEY' . '</b></td>
    <td><b>' . 'VALUE' . '</b></td>
';

				foreach($arrayVar as $key => $value) {
					if (empty($value)) {
						$value = '<pre>' . PHPWS_Debug::emptyVal($value) . '</pre>';
					} else if(is_array($value)) {
						$value = PHPWS_Debug::testArray($value, $displayTags, $show_recursive);
					} else if(is_object($value) || gettype($value) == 'object') {
						$value = PHPWS_Debug::testObject($value, $displayTags, $show_recursive);
					} else if($displayTags && is_string($value)) {
						$value = htmlspecialchars($value);
					} else if(is_bool($value)) {
						$value = '<pre>bool(TRUE)</pre>';
					} else {
						$value = '<pre>' . $value . '</pre>';
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

	public static function emptyVal($value)
	{
		switch (1) {
			case is_string($value):
				if ($value == '0') {
					return 'string(1)"0"';
				} else {
					return '""';
				}

			case is_array($value):
				return 'array()';

			case is_null($value):
				return 'NULL';

			case is_bool($value):
				return 'bool(FALSE)';

			case is_integer($value):
				return 'int(0)';
		}
	}

} // END CLASS PHPWS_Debug

function test($value, $exitAfter=FALSE, $show_recursive=FALSE)
{
	echo PHPWS_Debug::test($value, $show_recursive);
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
