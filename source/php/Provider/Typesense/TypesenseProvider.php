<?php

declare(strict_types=1);

namespace AlgoliaIndexTypesenseProvider\Provider\Typesense;

use Http\Client\Exception as HttpClientException;
use Typesense\Client;
use Typesense\Collection;
use Typesense\Exceptions\ObjectAlreadyExists;
use Typesense\Exceptions\TypesenseClientError;

/**
 * Typesense provider implementation backed by the official PHP SDK.
 */
class TypesenseProvider implements \AlgoliaIndex\Provider\AbstractProvider
{
    private Client $client;
    private string $collectionName;

    /**
     * @param Client $client Configured Typesense client instance.
     * @param string $collectionName Target collection name.
     */
    public function __construct(Client $client, string $collectionName)
    {
        $this->client = $client;
        $this->collectionName = $collectionName;
    }

    /**
     * Get a collection instance for the configured collection name.
     *
     * @return Collection
     */
    private function collection(): Collection
    {
        return $this->client->collections[$this->collectionName];
    }

    /**
     * Placeholder for provider index configuration.
     *
     * @return void
     */
    public static function getIndex()
    {
    }

    /**
     * Create the collection schema if it does not already exist.
     *
     * @param array $settings
     * @return void
     */
    public function setSettings(array $settings = [])
    {
        $locale = substr(get_locale(), 0, 2);
        $collectionData = \apply_filters('AlgoliaIndexTypesenseProvider/CollectionSchema', [
            'name' => $this->collectionName,
            'fields' => \apply_filters('AlgoliaIndexTypesenseProvider/Fields', [
                ['name' => 'post_title', 'type' => 'string', 'locale' => $locale],
                ['name' => 'post_excerpt', 'type' => 'string', 'locale' => $locale],
                ['name' => 'content', 'type' => 'string', 'locale' => $locale],
                ['name' => 'permalink', 'type' => 'string'],
                ['name' => 'tags', 'type' => 'string[]', 'facet' => true, 'optional' => true, 'locale' => $locale],
                [
                    'name' => 'categories',
                    'type' => 'string[]',
                    'facet' => true,
                    'optional' => true,
                    'locale' => $locale,
                ],
                ['name' => 'origin_site', 'type' => 'string', 'facet' => true],
                ['name' => '.*', 'type' => 'auto', 'locale' => $locale],
            ]),
        ]);

        try {
            $this->client->collections->create($collectionData);
        } catch (ObjectAlreadyExists) {
            return;
        } catch (TypesenseClientError | HttpClientException $exception) {
            error_log($exception->getMessage());
        }
    }

    /**
     * Execute a search query against the configured collection.
     *
     * @param string $query
     * @return array
     */
    public function search(string $query)
    {
        try {
            $response = $this->collection()->documents->search([
                'q' => $query,
                'query_by' => 'post_title,post_excerpt,content',
                'per_page' => 10,
            ]);
        } catch (TypesenseClientError | HttpClientException $exception) {
            error_log($exception->getMessage());
            return [];
        }

        $response['hits'] = array_map(static function (array $item) {
            return $item['document'];
        }, $response['hits'] ?? []);

        return $response;
    }

    /**
     * Truncate the configured collection.
     *
     * @return array
     */
    public function clearObjects()
    {
        try {
            return $this->collection()->documents->delete([
                'truncate' => true,
            ]);
        } catch (TypesenseClientError | HttpClientException $exception) {
            error_log($exception->getMessage());
            return [];
        }
    }

    /**
     * Delete a single object by id.
     *
     * @param string $objectId
     * @return array
     */
    public function deleteObject(string $objectId)
    {
        try {
            return $this->collection()->documents[$objectId]->delete();
        } catch (TypesenseClientError | HttpClientException $exception) {
            error_log($exception->getMessage());
            return [];
        }
    }

    /**
     * Delete multiple objects by id.
     *
     * @param array $objectIds
     * @return void
     */
    public function deleteObjects(array $objectIds)
    {
        foreach ($objectIds as $objectId) {
            $this->deleteObject($objectId);
        }
    }

    /**
     * Upsert a single object to Typesense.
     *
     * @param array $object
     * @param array $options
     * @return array|null
     */
    public function saveObject(array $object, array $options = [])
    {
        $data = \apply_filters('AlgoliaIndexTypesenseProvider/SaveObjectData', [
            ...$object,
            ...[
                'id' => $object['uuid'],
                'post_title' => html_entity_decode($object['post_title'] ?? ''),
                'post_excerpt' => html_entity_decode($object['post_excerpt'] ?? ''),
                'tags' => array_map(static fn($t) => html_entity_decode($t), $object['tags'] ?? []),
                'categories' => array_map(static fn($t) => html_entity_decode($t), $object['categories'] ?? []),
            ],
        ]);

        try {
            return $this->collection()->documents->upsert($data);
        } catch (TypesenseClientError | HttpClientException $exception) {
            error_log($exception->getMessage());
            error_log(\json_encode($data));
            return null;
        }
    }

    /**
     * Save multiple objects.
     *
     * @param array $objects
     * @param array $options
     * @return array
     */
    public function saveObjects(array $objects, array $options = [])
    {
        return array_map(function ($object) {
            return $this->saveObject($object);
        }, $objects);
    }

    /**
     * Retrieve multiple objects by id.
     *
     * @param array $objectIds
     * @return array
     */
    public function getObjects(array $objectIds): array
    {
        // error_log('Typesense: getObjects');
        return array_filter(
            array_map(function ($id) {
                try {
                    return $this->collection()->documents[$id]->retrieve();
                } catch (TypesenseClientError | HttpClientException $exception) {
                    error_log($exception->getMessage());
                    return null;
                }
            }, $objectIds),
            static function ($i) {
                return $i !== null;
            },
        );
    }

    /**
     * Typesense records should not be split.
     *
     * @return bool
     */
    public function shouldSplitRecord(): bool
    {
        return false;
    }
}
