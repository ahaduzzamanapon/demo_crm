<?php

namespace Config;

$routes = Services::routes();

$routes->get('google_docs', 'Google_Docs::index', ['namespace' => 'Google_Docs_Integration\Controllers']);
$routes->get('google_docs/(:any)', 'Google_Docs::$1', ['namespace' => 'Google_Docs_Integration\Controllers']);
$routes->add('google_docs/(:any)', 'Google_Docs::$1', ['namespace' => 'Google_Docs_Integration\Controllers']);
$routes->post('google_docs/(:any)', 'Google_Docs::$1', ['namespace' => 'Google_Docs_Integration\Controllers']);

$routes->get('google_docs_integration_settings', 'Google_Docs_Integration_settings::index', ['namespace' => 'Google_Docs_Integration\Controllers']);
$routes->get('google_docs_integration_settings/(:any)', 'Google_Docs_Integration_settings::$1', ['namespace' => 'Google_Docs_Integration\Controllers']);
$routes->post('google_docs_integration_settings/(:any)', 'Google_Docs_Integration_settings::$1', ['namespace' => 'Google_Docs_Integration\Controllers']);

$routes->get('google_docs_integration_updates', 'Google_Docs_Integration_Updates::index', ['namespace' => 'Google_Docs_Integration\Controllers']);
$routes->get('google_docs_integration_updates/(:any)', 'Google_Docs_Integration_Updates::$1', ['namespace' => 'Google_Docs_Integration\Controllers']);
