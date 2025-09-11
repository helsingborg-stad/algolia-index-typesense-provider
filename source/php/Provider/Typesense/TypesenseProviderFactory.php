<?php

namespace AlgoliaIndexTypesenseProvider\Provider\Typesense;

class TypesenseProviderFactory
{
    public static function createFromEnv()
    {
        return new TypesenseProvider(
            TYPESENSEINDEX_API_KEY, 
            TYPESENSEINDEX_API_URL, 
            defined('TYPESENSEINDEX_COLLECTION_NAME') && !empty(TYPESENSEINDEX_COLLECTION_NAME) ? TYPESENSEINDEX_COLLECTION_NAME : \AlgoliaIndex\Helper\Options::indexName(),
        );
    }
}