<?php

/**
 * Plugin Name:       Algolia Index Typesense Provider
 * Plugin URI:        https://github.com/helsingborg-stad/algolia-index-typesense-provider
 * Description:       Typesense search provider for Algolia Index plugin
 * Version:           1.0.0
 * Author:            Nikolas Ramstedt
 * Author URI:        https://github.com/helsingborg-stad
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       algolia-index-typesense-provider
 * Domain Path:       /languages
 */

 // Protect agains direct file access
if (! defined('WPINC')) {
    die;
}

define('ALGOLIA_INDEX_TYPESENSE_PROVIDER_PATH', plugin_dir_path(__FILE__));
define('ALGOLIA_INDEX_TYPESENSE_PROVIDER_URL', plugins_url('', __FILE__));
define('ALGOLIA_INDEX_TYPESENSE_PROVIDER_TEMPLATE_PATH', ALGOLIA_INDEX_TYPESENSE_PROVIDER_PATH . 'templates/');
define('ALGOLIA_INDEX_TYPESENSE_PROVIDER_TEXT_DOMAIN', 'algolia-index-typesense-provider');

load_plugin_textdomain(ALGOLIA_INDEX_TYPESENSE_PROVIDER_TEXT_DOMAIN, false, ALGOLIA_INDEX_TYPESENSE_PROVIDER_PATH . '/languages');

require_once ALGOLIA_INDEX_TYPESENSE_PROVIDER_PATH . 'Public.php';

// Register the autoloader
require __DIR__ . '/vendor/autoload.php';

// Acf auto import and export
add_action('acf/init', function () {
    $acfExportManager = new \AcfExportManager\AcfExportManager();
    $acfExportManager->setTextdomain('algolia-index-typesense-provider');
    $acfExportManager->setExportFolder(ALGOLIA_INDEX_TYPESENSE_PROVIDER_PATH . 'source/php/AcfFields/');
    $acfExportManager->autoExport(array(
        'algolia-index-typesense-provider'        => 'group_68bfb035c379f',
    ));
    $acfExportManager->import();
});

// Start application
new AlgoliaIndexTypesenseProvider\App();
