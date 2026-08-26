<?php

declare(strict_types=1);

namespace AlgoliaIndexTypesenseProvider\Provider\Typesense;

use AlgoliaIndexTypesenseProvider\Helper\Options;
use Typesense\Client;

/**
 * Factory for creating configured Typesense providers.
 */
class TypesenseProviderFactory
{
    /**
     * Build a TypesenseProvider using environment and option configuration.
     *
     * @return TypesenseProvider
     */
    public static function createFromEnv()
    {
        $urlParts = parse_url((string) Options::apiUrl()) ?: [];
        $protocol = $urlParts['scheme'] ?? 'https';

        $client = new Client([
            'api_key' => (string) Options::apiKey(),
            'nodes' => [[
                'host' => $urlParts['host'] ?? '',
                'port' => $urlParts['port'] ?? ($protocol === 'https' ? 443 : 8108),
                'protocol' => $protocol,
            ]],
            'connection_timeout_seconds' => 2,
        ]);

        return new TypesenseProvider($client, Options::collectionName());
    }
}
