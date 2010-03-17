<?php
#ini_set('include_path', '.');
/**
 * Usage: test.php?driver=XX
 * 
 */
// as we're testing we want high error reporting
error_reporting(E_ALL);

// Where we'll put resulting image and HTML files...
define('TEST_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('TEST_IMAGE_DIR', TEST_DIR . 'images' . DIRECTORY_SEPARATOR);
define('TEST_OUTPUT_DIR', TEST_DIR . 'tmp' . DIRECTORY_SEPARATOR);

define('FONTS', '/usr/share/fonts/');
define('FONTS_TTF', FONTS . DIRECTORY_SEPARATOR . 'corefonts' . DIRECTORY_SEPARATOR);

#$lib_path = array('IM' =>)


$driver = $_GET['driver'];
$image = $_GET['image'];
if (!defined('IMAGE_TRANSFORM_LIB_PATH')) {
    define('IMAGE_TRANSFORM_LIB_PATH', '/usr/bin/');
}

if ($driver == 'IM' || $driver == 'NetPBM') {
    // Assume binaries are in your path
    #define('IMAGE_TRANSFORM_LIB_PATH', '');
}

require_once 'Image/Transform.php';
$im = Image_Transform::factory($driver);
#var_dump($im);
if (PEAR::isError($im)) {
    die($im->message);
}
// Load the image
$im->load(TEST_IMAGE_DIR . $image);

/*
 * Initialise variables
 */
$cmd = isset($_GET['cmd']) ? $_GET['cmd'] : '';
$angle = isset($_GET['angle']) ? intval($_GET['angle']) : 0;
$width = isset($_GET['width']) ? intval($_GET['width']) : $im->getImageWidth();
$height = isset($_GET['height']) ? intval($_GET['height']) : $im->getImageHeight();
// Text to be added
$text = isset($_GET['text']) ? $_GET['text'] : 'Sample Text';

/*
 * Now start.  Switch cmd to find which test to run
 */
$cmds = explode('|', $cmd);
foreach($cmds as $cmd){
	switch($cmd){
        case 'addText':
            $im->addText(array('text' => $text, 'font' => FONTS_TTF . '/arial.ttf'));
            break;
    	case 'resize': 
    		$im->resize($width/2, $height/2);
    		break;
    	case 'rotate': 
    		$im->rotate($angle);
    		break;
        case 'scaleByX':
            $im->scaleByX($width/2);
            break;
        case 'scaleByY':
            $im->scaleByY($width/2);
            break;
        case 'scaleByFactor':
            $im->scaleByFactor(0.5);
            break;
        case 'scaleByPercentage':
            $im->scaleByPercentage(50);
            break;
        default:
    		trigger_error('No command specified... aborting');
    } // switch
}

$im->display(null, 100);
#$im->save($image_file . '-' . $driver);
$im->free();
/*
 * Now load the image, and:
 *  a) add text and then resize
 *  a) resize and then add text
 * Check the font size is different
 */

/*
 * Rotate the image
 */

?>
