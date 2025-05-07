<?php

namespace AlgoliaIndexTypesenseProvider\Provider\Typesense;
class TypesenseProvider implements \AlgoliaIndex\Provider\AbstractProvider
{
    private string $apiKey;
    private string $apiUrl;
    private string $collectionName;
    private array $settings;

    public function __construct(
        string $apiKey, 
        string $apiUrl, 
        string $collectionName, 
        array $settings = []
    ) {
        $this->apiKey = $apiKey;
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->collectionName = $collectionName;
        $this->setSettings($settings);
    }
    
    private function sendRequest(string $method, string $endpoint, array $data = [], array $customHeaders = []): mixed
    {
        $url = "{$this->apiUrl}{$endpoint}" . ($method === 'GET' && !empty($data) ? '?' . http_build_query($data) : '');

        $defaultHeaders = [
            "Content-Type: application/json",
            "X-TYPESENSE-API-KEY: {$this->apiKey}"
        ];

        $headers = array_merge($defaultHeaders, $customHeaders);

        $options = [
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $headers),
                'content' => $method === 'POST' && !empty($data) ? json_encode($data) : null,
                'ignore_errors' => true
            ]
        ];
        
        $context = stream_context_create($options);
        
        $response = file_get_contents($url, false, $context);
        
        return  [
            'result' => \json_decode($response, true), 
            'error'=> $response === false ? \error_get_last() : null,
            'statusCode' => isset($http_response_header[0]) ? (int) explode(' ', $http_response_header[0])[1] : 0
        ];
    }

    public static function getIndex() 
    {        
    }

    public function setSettings(array $settings) 
    {
        $collectionData = [
            'name' => $this->collectionName,
            'fields' => [
                ['name' => '.*' , 'type' => 'auto']
            ]
        ];
        $response = $this->sendRequest('POST', '/collections', $collectionData);
        if (!empty($response['error'])) {
            error_log(\json_encode($response));
            $allowedErrors = [409];
            if (!in_array($response['statusCode'], $allowedErrors)) {
                error_log(\json_encode($response));
            }
        }
    }

    public function search(string $query) 
    {
        $response = $this->sendRequest('GET', "/collections/{$this->collectionName}/documents/search", [
            'q' => $query,
            'query_by' => 'content,post_title',
            'per_page' => 10
        ]);

        if ($response['error']) {
            error_log(\json_encode($response['error']));
        }

        $response['result']['hits'] = array_map(
            function(array $item) {
                return $item['document'];
            },
            $response['result']['hits'] ?? []
        );

        return $response['result'];
    }

    public function clearObjects() 
    {
        $response = $this->sendRequest('DELETE', "/collections/{$this->collectionName}/documents?truncate=true");
        if ($response['error']) {
            error_log(\json_encode($response['error']));
        }
        return $response['result'];
    }

    public function deleteObject(string $objectId) 
    {
        $response = $this->sendRequest('DELETE', "/collections/{$this->collectionName}/documents/{$objectId}");
        if ($response['error']) {
            error_log(\json_encode($response['error']));
        }
        return $response['result'];
    }

    public function deleteObjects(array $objectIds) 
    {
        foreach ($objectIds as $objectId) {
            $this->deleteObject($objectId);
        }
    }

    public function saveObject(array $object, array $options = []) 
    {
        $data = [
            ...$object, 
            ...[
                'id' => $object['uuid'],
                'tags' => $object['tags'] ?? [],
                'categories' => $object['categories'] ?? [],
                'thumbnail_alt' => $object['thumbnail_alt'] && \is_string($object['thumbnail_alt']) ? $object['thumbnail_alt'] : '',
            ]
        ];
        error_log(\json_encode($data));
        $response = $this->sendRequest(
            'POST', 
            "/collections/{$this->collectionName}/documents", 
            $data
        );
        if ($response['error']) {
            error_log($response['statusCode']);
            error_log(\json_encode($data));
            return null;
        }
        return $response['result'];
    }

    public function saveObjects(array $objects, array $options = []) 
    {
        return array_map(function ($object) {
            return $this->saveObject($object);
        }, $objects);
    }

    public function getObjects(array $objectIds): array 
    {
        return array_filter(
            array_map(function ($id) {
                $response = $this->sendRequest("GET", "/collections/{$this->collectionName}/documents/{$id}");
                if ($response['error']) {
                    error_log(\json_encode($response['error']));
                    return null;
                }
                return $response['result'];
            }, $objectIds),
            function ($i) {
                return $i !== null;
            }
        );
    }
}
