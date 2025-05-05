<?php

namespace AlgoliaIndexTypesenseProvider;

class App
{
    public function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueueStyles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueueScripts'));

        $this->cacheBust = new \AlgoliaIndexTypesenseProvider\Helper\CacheBust();
    }

    /**
     * Enqueue required style
     * @return void
     */
    public function enqueueStyles()
    {
        wp_register_style(
            'algolia-index-typesense-provider-css',
            ALGOLIA_INDEX_TYPESENSE_PROVIDER_URL . '/dist/' .
            $this->cacheBust->name('css/algolia-index-typesense-provider.css')
        );

        wp_enqueue_style('algolia-index-typesense-provider-css');
    }

    /**
     * Enqueue required scripts
     * @return void
     */
    public function enqueueScripts()
    {
        wp_register_script(
            'algolia-index-typesense-provider-js',
            ALGOLIA_INDEX_TYPESENSE_PROVIDER_URL . '/dist/' .
            $this->cacheBust->name('js/algolia-index-typesense-provider.js')
        );

        wp_enqueue_script('algolia-index-typesense-provider-js');
    }
}
