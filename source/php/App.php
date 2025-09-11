<?php

namespace AlgoliaIndexTypesenseProvider;

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
                || !defined('TYPESENSEINDEX_API_KEY') 
                || empty(TYPESENSEINDEX_API_KEY)){
                return $config;
            }

            $parts = parse_url(TYPESENSEINDEX_APPLICATION_ID);
            return array_merge(
                $config,
                [
                    'type'              => 'typesense',
                    'host'              => isset($parts['host']) ? $parts['host'] : null,
                    'port'              => isset($parts['port']) ? $parts['port'] : 443,
                    'protocol'          => isset($parts['scheme']) ? $parts['scheme'] : 'https',
                    'apiKey'            => TYPESENSEINDEX_API_KEY,
                    'collectionName'    => defined('TYPESENSEINDEX_INDEX_NAME') && !empty(TYPESENSEINDEX_INDEX_NAME) ? TYPESENSEINDEX_INDEX_NAME : \AlgoliaIndex\Helper\Options::indexName(),
                ]
            );
        });
    }

    public function notices()
    {
        $conditions = [
            [!is_plugin_active("algolia-index/algolia-index.php"), __("AlgoliaIndex plugin is not activated.", "algoliaindex-typesense-provider")],
            [!defined("TYPESENSEINDEX_API_KEY") || empty(TYPESENSEINDEX_API_KEY), __("TYPESENSEINDEX_API_KEY is not defined.", "algoliaindex-typesense-provider")],
            [!defined("TYPESENSEINDEX_APPLICATION_ID") || empty(TYPESENSEINDEX_APPLICATION_ID), __("TYPESENSEINDEX_APPLICATION_ID is not defined.", "algoliaindex-typesense-provider")],
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
