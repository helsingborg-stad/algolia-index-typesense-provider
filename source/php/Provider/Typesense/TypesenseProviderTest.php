<?php

declare(strict_types=1);

namespace AlgoliaIndexTypesenseProvider\Provider\Typesense;

use PHPUnit\Framework\TestCase;
use Typesense\Client;
use Typesense\Collection;
use Typesense\Collections;
use Typesense\Document;
use Typesense\Documents;

class TypesenseProviderTest extends TestCase
{
    public function testSearchMapsHitsToDocuments(): void
    {
        $documents = $this->getMockBuilder(Documents::class)->disableOriginalConstructor()->getMock();
        $documents->expects($this->once())
            ->method('search')
            ->with([
                'q' => 'query',
                'query_by' => 'post_title,post_excerpt,content',
                'per_page' => 10,
            ])
            ->willReturn([
                'found' => 2,
                'hits' => [
                    ['document' => ['id' => 1]],
                    ['document' => ['id' => 2]],
                ],
            ]);

        $collection = $this->getMockBuilder(Collection::class)->disableOriginalConstructor()->getMock();
        $collection->documents = $documents;

        $collections = $this->getMockBuilder(Collections::class)->disableOriginalConstructor()->getMock();
        $collections->method('offsetGet')->willReturn($collection);

        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
        $client->collections = $collections;

        $provider = new TypesenseProvider($client, 'posts');

        $result = $provider->search('query');

        $this->assertSame([['id' => 1], ['id' => 2]], $result['hits']);
        $this->assertSame(2, $result['found']);
    }

    public function testSaveObjectUpsertsDecodedPayload(): void
    {
        $documents = $this->getMockBuilder(Documents::class)->disableOriginalConstructor()->getMock();
        $documents->expects($this->once())
            ->method('upsert')
            ->with($this->callback(function (array $payload) {
                $this->assertSame('uuid-1', $payload['id']);
                $this->assertSame('Hello & Welcome', $payload['post_title']);
                $this->assertSame('Excerpt > Test', $payload['post_excerpt']);
                $this->assertSame(['Tag One', 'Tag Two'], $payload['tags']);
                $this->assertSame(['Category'], $payload['categories']);
                return true;
            }))
            ->willReturn(['id' => 'uuid-1']);

        $collection = $this->getMockBuilder(Collection::class)->disableOriginalConstructor()->getMock();
        $collection->documents = $documents;

        $collections = $this->getMockBuilder(Collections::class)->disableOriginalConstructor()->getMock();
        $collections->method('offsetGet')->willReturn($collection);

        $client = $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock();
        $client->collections = $collections;

        $provider = new TypesenseProvider($client, 'posts');

        $provider->saveObject([
            'uuid' => 'uuid-1',
            'post_title' => 'Hello &amp; Welcome',
            'post_excerpt' => 'Excerpt &gt; Test',
            'tags' => ['Tag One', 'Tag Two'],
            'categories' => ['Category'],
        ]);
    }
}
