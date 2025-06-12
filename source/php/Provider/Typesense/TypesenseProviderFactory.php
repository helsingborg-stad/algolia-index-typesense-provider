<?php

namespace AlgoliaIndexTypesenseProvider\Provider\Typesense;

class TypesenseProviderFactory
{
    public static function createFromEnv()
    {
        return new TypesenseProvider(
            TYPESENSEINDEX_API_KEY, 
            TYPESENSEINDEX_APPLICATION_ID, 
            defined('TYPESENSEINDEX_INDEX_NAME') && !empty(TYPESENSEINDEX_INDEX_NAME) ? TYPESENSEINDEX_INDEX_NAME : \AlgoliaIndex\Helper\Options::indexName(),
        );
    }
}