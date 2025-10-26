<?php

namespace Config;

$routes = Services::routes();



$routes->get('google_meet_meetings', 'Google_Meet_Meetings::index', ['namespace' => 'Google_Meet_Integration\Controllers']);
$routes->get('google_meet_meetings/(:any)', 'Google_Meet_Meetings::$1', ['namespace' => 'Google_Meet_Integration\Controllers']);
$routes->add('google_meet_meetings/(:any)', 'Google_Meet_Meetings::$1', ['namespace' => 'Google_Meet_Integration\Controllers']);
$routes->post('google_meet_meetings/(:any)', 'Google_Meet_Meetings::$1', ['namespace' => 'Google_Meet_Integration\Controllers']);

$routes->get('google_meet_integration_settings', 'Google_Meet_Integration_settings::index', ['namespace' => 'Google_Meet_Integration\Controllers']);
$routes->get('google_meet_integration_settings/(:any)', 'Google_Meet_Integration_settings::$1', ['namespace' => 'Google_Meet_Integration\Controllers']);
$routes->post('google_meet_integration_settings/(:any)', 'Google_Meet_Integration_settings::$1', ['namespace' => 'Google_Meet_Integration\Controllers']);

$routes->get('google_meet_integration_updates', 'Google_Meet_Integration_Updates::index', ['namespace' => 'Google_Meet_Integration\Controllers']);
$routes->get('google_meet_integration_updates/(:any)', 'Google_Meet_Integration_Updates::$1', ['namespace' => 'Google_Meet_Integration\Controllers']);
