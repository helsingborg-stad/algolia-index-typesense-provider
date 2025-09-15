<?php

namespace AlgoliaIndexTypesenseProvider;

use AlgoliaIndexTypesenseProvider\Helper\Options;
use AlgoliaIndexTypesenseProvider\Provider\Typesense\TypesenseProviderFactory;

class App
{
    public function __construct()
    {
        if (!$this->isConfigured()) {
            add_action("admin_notices", [$this, "showAdminNotice"]);
            return;
        }

        add_filter("AlgoliaIndex/Options/IsConfigured", function($isConfigured) {return false;}, 10, 1);
        add_filter("AlgoliaIndex/Provider/Factory", [$this, "registerProvider"]);

        // Plugin(helsingborg-stad/algolia-index-js-searchpage-addon) integration
        add_filter('AlgoliaIndex/SearchConfig', function($config) {
            if (get_field('algolia_index_search_provider', 'option') !== 'typesense'
                || !Options::apiKey()
                || !Options::apiUrl()) {
                return $config;
            }

            $parts = parse_url(Options::apiUrl());
            return array_merge(
                $config,
                [
                    'type'              => 'typesense',
                    'host'              => isset($parts['host']) ? $parts['host'] : null,
                    'port'              => isset($parts['port']) ? $parts['port'] : 443,
                    'protocol'          => isset($parts['scheme']) ? $parts['scheme'] : 'https',
                    'apiKey'            => Options::apiKey(),
                    'collectionName'    => Options::collectionName(),
                ]
            );
        });
    }

    public function notices()
    {
        $conditions = [
            [!is_plugin_active("algolia-index/algolia-index.php"), __("AlgoliaIndex plugin is not activated.", "algoliaindex-typesense-provider")],
            [!Options::apiKey(), __("TYPESENSEINDEX_API_KEY is not defined.", "algoliaindex-typesense-provider")],
            [!Options::apiUrl(), __("TYPESENSEINDEX_API_URL is not defined.", "algoliaindex-typesense-provider")],
            [!class_exists("\AlgoliaIndex\App"), __("AlgoliaIndex class not found.", "algoliaindex-typesense-provider")],
        ];
        
        return array_filter(array_map(function($item) {
            [$condition, $message] = $item;
            return $condition ? $message : null;
        }, $conditions));
    }

    public function isConfigured()
    {
        return empty($this->notices());
    }

    public function showAdminNotice()
    {
        echo "<div class='notice notice-error'><p>";
        echo _e("Algolia Index Typesense Provider (Plugin) - The following issues need to be resolved:", "algoliaindex-typesense-provider") . "<br>";
        foreach ($this->notices() as $notice) {
            echo esc_html($notice) . "<br>";
        }
        echo "</p></div>";
    }

    public function registerProvider($providers)
    {
        $providers['typesense'] = fn() => TypesenseProviderFactory::createFromEnv();
        return $providers;
    }
}
