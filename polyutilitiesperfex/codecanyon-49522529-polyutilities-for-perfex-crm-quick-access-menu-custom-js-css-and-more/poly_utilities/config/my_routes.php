<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Configure routes for custom menu links with type iframe on the client side.
 */
$base = 'poly_utilities/article/details';
$route['article/(:any)'] = $base . '/$1';
$route[$base . '/(:any)'] = 'poly_utilities/redirect/utilities_article_details/$1';

$route['poly_utilities/media'] = 'poly_utilities/media/index';
$route['poly_utilities/announcements'] = 'poly_utilities/media/announcements';
$route['poly_utilities/download_module'] = 'poly_utilities/poly_utilities/download_module';

// Multiple Companies - Client Side Routes
$route['poly_utilities/multiple_companies_clients/change_auth/(:num)'] = 'poly_utilities/multiple_companies_clients/change_auth/$1';
$route['poly_utilities/customer_addresses/(:any)'] = 'poly_utilities/customer_addresses/$1';
$route['admin/poly_utilities/customer_addresses/(:any)'] = 'poly_utilities/customer_addresses/$1';
$route['admin/poly_utilities/customer_addresses/(:any)'] = 'poly_utilities/customer_addresses/$1';
