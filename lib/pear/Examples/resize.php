<?php
require_once 'Image/Transform.php';


// factory pattern - returns an object
$a = Image_Transform::factory('GD');

// load the image file
$a->load("teste.jpg");


// scale image by percentage - 40% of its original size
$a->scalebyPercentage(40);

// displays the image
$a->display();

