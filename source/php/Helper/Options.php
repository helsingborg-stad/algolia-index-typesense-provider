<?php

declare(strict_types=1);


namespace AlgoliaIndexTypesenseProvider\Helper;

class Options
{
    public static function apiUrl(): null|string
    {
        if (defined('TYPESENSEINDEX_API_URL') && !empty(TYPESENSEINDEX_API_URL)) {
            return TYPESENSEINDEX_API_URL;
        }

        return get_field('algolia_index_typesense_api_url', 'option') ?: null;
    }

    public static function apiKey(): null|string
    {
        if (defined('TYPESENSEINDEX_API_KEY') && !empty(TYPESENSEINDEX_API_KEY)) {
            return TYPESENSEINDEX_API_KEY;
        }

        return get_field('algolia_index_typesense_api_key', 'option') ?: null;
    }

    public static function publicApiKey(): null|string
    {
        if (defined('TYPESENSEINDEX_PUBLIC_API_KEY') && !empty(TYPESENSEINDEX_PUBLIC_API_KEY)) {
            return TYPESENSEINDEX_PUBLIC_API_KEY;
        }

        return get_field('algolia_index_typesense_public_api_key', 'option') ?: null;
    }

    public static function collectionName(): string
    {
        if (defined('TYPESENSEINDEX_COLLECTION_NAME') && !empty(TYPESENSEINDEX_COLLECTION_NAME)) {
            return TYPESENSEINDEX_COLLECTION_NAME;
        }

        return (
            get_field('algolia_index_typesense_collection_name', 'option') ?: \AlgoliaIndex\Helper\Options::indexName()
        );
    }
}
