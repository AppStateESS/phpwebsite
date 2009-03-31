If freecap is not working for you, make sure your config.php does NOT
contain the following:

/**
 * To keep this file a little tidier, file and image types are listed
 * in the file_types.php file. Make sure you know how to edit
 * arrays before altering the file.
 */ 
include 'config/core/file_types.php';

Just delete it all.

Make sure that the following IS in your config.php file:
define('ALLOW_CAPTCHA', true);
define('CAPTCHA_NAME', 'freecap');

phpWebSite has altered the following filenames to allow them to be copied to
branches.

ht_freecap_font1.gdf
ht_freecap_font2.gdf
ht_freecap_font3.gdf
ht_freecap_font4.gdf
ht_freecap_font5.gdf
ht_freecap_im1.jpg
ht_freecap_im2.jpg
ht_freecap_im3.jpg
ht_freecap_im4.jpg
ht_freecap_im5.jpg
ht_freecap_words

Previously, these files had a period prefix.

Final note: FreeCap has 4 background types. If you use bg_type = 3 (the
default) you may encounter memory problems with PHP 4. Increase your
limit to 16mb or more. PHP 5 doesn't seem to have this problem.


