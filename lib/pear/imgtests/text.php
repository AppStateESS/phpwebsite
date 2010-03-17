<?php

/**
 * Main page to display the tests for Image_Transform
 *
 * @version $Id: text.php 162897 2004-07-08 21:42:28Z reywob $
 * @copyright 2003 Peter Bowyer
 */
 
// Drivers to test.
$drivers = array(
                 'IM',
                 'GD',
                 'NetPBM',
                 'Imagick2'
                 );
$image = 'image1.jpg';


$num_drivers = count($drivers);
print '<table border="1">';
// Print the library title above each column
print '<tr>';
for($i = 0; $i < $num_drivers; $i++){
    print '<td>' . $drivers[$i] . '</td>';
} // for
print '</tr>';
/*
// Print a header and then the test
$angles = array(90, 45, 30, 12);
for($i = 0; $i < count($angles); $i++){
	print '<tr><td bgcolor="#CCCCCC" colspan="' . $num_drivers . '">Rotated at ' . $angles[$i] . ' degrees</td></tr>';
    print '<tr>';
    for($j = 0; $j < $num_drivers; $j++){
        print '<td><img src="test.php?driver=' . $drivers[$j] . '&amp;image=' . $image . '&amp;cmd=rotate&amp;angle=' . $angles[$i] . '"></td>';
    } // for
    print '</tr>';
} // for
*/
print '<tr><td colspan="' . $num_drivers . '">AddText test</td></tr>';
print '<tr>';
for($i = 0; $i < $num_drivers; $i++){
    print '<td><img src="test.php?driver=' . $drivers[$i] . '&amp;image=' . $image . '&amp;cmd=addText&amp;text=This+is+text"></td>';
} // for
print '</tr>';


print '</table>';


?>