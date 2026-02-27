<?php

declare(strict_types=1);

namespace {
    /**
     * Mimics the WordPress apply_filters helper during tests.
     */
    function apply_filters(string $hook, $value)
    {
        return $value;
    }

    /**
     * Provides a deterministic locale value for tests.
     */
    function get_locale(): string
    {
        return 'en_US';
    }
}

namespace AlgoliaIndex\Provider {
    /**
     * Minimal stub of the AlgoliaIndex provider contract for tests.
     */
    interface AbstractProvider
    {
    }
}

namespace AlgoliaIndexTypesenseProvider\Provider\Typesense {

    use PHPUnit\Framework\TestCase;

    /**
     * @covers \AlgoliaIndexTypesenseProvider\Provider\Typesense\TypesenseProvider
     */
    class TypesenseProviderTest extends TestCase
    {
        /**
         * Ensures saveObject performs an upsert and normalizes document data.
         */
        public function testSaveObjectUsesUpsertActionAndTransformsData(): void
        {
            $provider = new class ('key', 'https://typesense.test', 'test-collection') extends TypesenseProvider {
                public array $lastRequest = [];

                /**
                 * Capture request parameters instead of performing HTTP requests.
                 *
                 * @param string $method
                 * @param string $endpoint
                 * @param array  $data
                 * @param array  $customHeaders
                 *
                 * @return array<string, mixed>
                 */
                protected function sendRequest(string $method, string $endpoint, array $data = [], array $customHeaders = []): mixed
                {
                    $this->lastRequest = compact('method', 'endpoint', 'data', 'customHeaders');

                    return [
                        'result' => ['ok' => true],
                        'error' => null,
                        'statusCode' => 200,
                    ];
                }
            };

            $provider->saveObject([
                'uuid' => 'abc-123',
                'post_title' => 'Foo &amp; Bar',
                'post_excerpt' => 'Excerpt &amp; copy',
                'tags' => ['Tag &amp; One'],
                'categories' => ['Cat &amp; One'],
            ]);

            self::assertSame('POST', $provider->lastRequest['method']);
            self::assertSame('/collections/test-collection/documents?action=upsert', $provider->lastRequest['endpoint']);
            self::assertSame('abc-123', $provider->lastRequest['data']['id']);
            self::assertSame('Foo & Bar', $provider->lastRequest['data']['post_title']);
            self::assertSame('Excerpt & copy', $provider->lastRequest['data']['post_excerpt']);
            self::assertSame(['Tag & One'], $provider->lastRequest['data']['tags']);
            self::assertSame(['Cat & One'], $provider->lastRequest['data']['categories']);
        }
    }
}
