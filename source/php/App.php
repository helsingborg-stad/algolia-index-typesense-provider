<?php

declare(strict_types=1);

namespace AlgoliaIndexTypesenseProvider;

use AlgoliaIndexTypesenseProvider\Helper\Options;
use AlgoliaIndexTypesenseProvider\Provider\Typesense\TypesenseProviderFactory;

class App
{
    public function __construct()
    {
        if (!$this->isConfigured()) {
            add_action('admin_notices', [$this, 'showAdminNotice']);
            return;
        }

        add_filter(
            'AlgoliaIndex/Options/IsConfigured',
            static function ($isConfigured) {
                return false;
            },
            10,
            1,
        );
        add_filter('AlgoliaIndex/Provider/Factory', [$this, 'registerProvider']);

        // Plugin(helsingborg-stad/algolia-index-js-searchpage-addon) integration
        add_filter('AlgoliaIndex/SearchConfig', static function ($config) {
            if (get_field('algolia_index_search_provider', 'option') !== 'typesense' || !Options::publicApiKey() || !Options::apiUrl()) {
                return $config;
            }

            $parts = parse_url(Options::apiUrl());
            return array_merge($config, [
                'type' => 'typesense',
                'host' => isset($parts['host']) ? $parts['host'] : null,
                'port' => isset($parts['port']) ? $parts['port'] : 443,
                'protocol' => isset($parts['scheme']) ? $parts['scheme'] : 'https',
                // SearchConfig is sent to browser JavaScript; keep the admin key server-side.
                'apiKey' => Options::publicApiKey(),
                'collectionName' => Options::collectionName(),
            ]);
        });

        add_filter('WpSecurity/Csp', static function ($domains) {
            if (get_field('algolia_index_search_provider', 'option') !== 'typesense') {
                return $domains;
            }

            $parts = parse_url((string) Options::apiUrl());
            if (!is_array($parts) || !isset($parts['scheme'], $parts['host']) || !in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
                return $domains;
            }

            $origin = strtolower($parts['scheme']) . '://' . $parts['host'];
            if (isset($parts['port'])) {
                $origin .= ':' . $parts['port'];
            }

            $domains['connect-src'] ??= [];
            $domains['connect-src'][] = $origin;

            return $domains;
        });
    }

    public function notices()
    {
        $conditions = [
            [
                !$this->isPluginActive('algolia-index/algolia-index.php'),
                __('AlgoliaIndex plugin is not activated.', 'algoliaindex-typesense-provider'),
            ],
            [!Options::apiKey(), __('TYPESENSEINDEX_API_KEY is not defined.', 'algoliaindex-typesense-provider')],
            [!Options::apiUrl(), __('TYPESENSEINDEX_API_URL is not defined.', 'algoliaindex-typesense-provider')],
            [
                !class_exists("\AlgoliaIndex\App"),
                __('AlgoliaIndex class not found.', 'algoliaindex-typesense-provider'),
            ],
        ];

        return array_filter(array_map(static function ($item) {
            [$condition, $message] = $item;
            return $condition ? $message : null;
        }, $conditions));
    }

    public function isConfigured()
    {
        return empty($this->notices());
    }

    /**
     * Network-active plugins can boot before WP-CLI or a front-end request has
     * loaded the wp-admin plugin helpers.
     */
    private function isPluginActive(string $plugin): bool
    {
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        return \is_plugin_active($plugin);
    }

    public function showAdminNotice()
    {
        echo "<div class='notice notice-error'><p>";
        echo
            _e(
                'Algolia Index Typesense Provider (Plugin) - The following issues need to be resolved:',
                'algoliaindex-typesense-provider',
            ) . '<br>'
        ;
        foreach ($this->notices() as $notice) {
            echo esc_html($notice) . '<br>';
        }
        echo '</p></div>';
    }

    public function registerProvider($providers)
    {
        $providers['typesense'] = static fn() => TypesenseProviderFactory::createFromEnv();
        return $providers;
    }
}
