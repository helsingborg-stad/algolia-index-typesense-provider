<?php

// Get around direct access blockers.
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/../../../');
}

define('ALGOLIA_INDEX_TYPESENSE_PROVIDER_PATH', __DIR__ . '/../../../');
define('ALGOLIA_INDEX_TYPESENSE_PROVIDER_URL', 'https://example.com/wp-content/plugins/' . 'modularity-algolia-index-typesense-provider');
define('ALGOLIA_INDEX_TYPESENSE_PROVIDER_TEMPLATE_PATH', ALGOLIA_INDEX_TYPESENSE_PROVIDER_PATH . 'templates/');


// Register the autoloader
$loader = require __DIR__ . '/../../../vendor/autoload.php';
$loader->addPsr4('AlgoliaIndexTypesenseProvider\\Test\\', __DIR__ . '/../php/');

require_once __DIR__ . '/PluginTestCase.php';
