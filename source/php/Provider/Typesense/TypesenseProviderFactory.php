<?php

declare(strict_types=1);


namespace AlgoliaIndexTypesenseProvider\Provider\Typesense;

use AlgoliaIndexTypesenseProvider\Helper\Options;

class TypesenseProviderFactory
{
    public static function createFromEnv()
    {
        return new TypesenseProvider(Options::apiKey(), Options::apiUrl(), Options::collectionName());
    }
}
