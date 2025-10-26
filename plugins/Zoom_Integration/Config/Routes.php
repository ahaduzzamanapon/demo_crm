<?php

namespace Config;

$routes = Services::routes();

$routes->get('zoom_meetings', 'Zoom_Meetings::index', ['namespace' => 'Zoom_Integration\Controllers']);
$routes->get('zoom_meetings/(:any)', 'Zoom_Meetings::$1', ['namespace' => 'Zoom_Integration\Controllers']);
$routes->add('zoom_meetings/(:any)', 'Zoom_Meetings::$1', ['namespace' => 'Zoom_Integration\Controllers']);
$routes->post('zoom_meetings/(:any)', 'Zoom_Meetings::$1', ['namespace' => 'Zoom_Integration\Controllers']);

$routes->get('zoom_integration_settings', 'Zoom_Integration_settings::index', ['namespace' => 'Zoom_Integration\Controllers']);
$routes->get('zoom_integration_settings/(:any)', 'Zoom_Integration_settings::$1', ['namespace' => 'Zoom_Integration\Controllers']);
$routes->post('zoom_integration_settings/(:any)', 'Zoom_Integration_settings::$1', ['namespace' => 'Zoom_Integration\Controllers']);

$routes->get('zoom_integration_updates', 'Zoom_Integration_Updates::index', ['namespace' => 'Zoom_Integration\Controllers']);
$routes->get('zoom_integration_updates/(:any)', 'Zoom_Integration_Updates::$1', ['namespace' => 'Zoom_Integration\Controllers']);
