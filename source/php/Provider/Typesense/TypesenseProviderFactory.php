<?php

namespace AlgoliaIndexTypesenseProvider\Provider\Typesense;

class TypesenseProviderFactory
{
    public static function createFromEnv()
    {
        return new TypesenseProvider(
            TYPESENSEINDEX_API_KEY, 
            TYPESENSEINDEX_APPLICATION_ID, 
            TYPESENSEINDEX_INDEX_NAME ?? AlgoliaIndex\Helper\Options::indexName()
        );
    }
}