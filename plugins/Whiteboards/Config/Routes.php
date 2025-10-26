<?php

namespace Config;

$routes = Services::routes();

$routes->get('whiteboards', 'Whiteboards::index', ['namespace' => 'Whiteboards\Controllers']);
$routes->get('whiteboards/(:any)', 'Whiteboards::$1', ['namespace' => 'Whiteboards\Controllers']);
$routes->add('whiteboards/(:any)', 'Whiteboards::$1', ['namespace' => 'Whiteboards\Controllers']);
$routes->post('whiteboards/(:any)', 'Whiteboards::$1', ['namespace' => 'Whiteboards\Controllers']);

$routes->get('whiteboards_updates', 'Whiteboards_Updates::index', ['namespace' => 'Whiteboards\Controllers']);
$routes->get('whiteboards_updates/(:any)', 'Whiteboards_Updates::$1', ['namespace' => 'Whiteboards\Controllers']);
