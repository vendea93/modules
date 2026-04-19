<?php

defined('BASEPATH') or exit('No direct script access allowed');

$route['flexacademy/courses'] = 'clientflexacademy/index';
$route['flexacademy/course/details/(:any)'] = 'clientflexacademy/details/$1';
$route['flexacademy/course/player/(:any)'] = 'clientflexacademy/course_player/$1';
$route['flexacademy/lesson/(:num)'] = 'clientflexacademy/lesson/$1';
$route['flexacademy/my-courses'] = 'clientflexacademy/my_courses';
$route['flexacademy/client/certificate/(:any)'] = 'clientflexacademy/certificate/$1';
$route['flexacademy/cart'] = 'clientflexacademy/cart';
$route['flexacademy/ajax'] = 'clientflexacademy/ajax';