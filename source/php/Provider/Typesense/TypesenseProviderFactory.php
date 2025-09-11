<?php

namespace AlgoliaIndexTypesenseProvider\Provider\Typesense;

use AlgoliaIndexTypesenseProvider\Helper\Options;

class TypesenseProviderFactory
{
    public static function createFromEnv()
    {
        return new TypesenseProvider(
            Options::apiKey(), 
            Options::apiUrl(), 
            Options::collectionName()
        );
    }
}