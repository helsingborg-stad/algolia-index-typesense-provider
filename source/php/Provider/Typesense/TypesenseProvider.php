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
        // \error_log(\json_encode([
        //     'apiKey' => $apiKey,
        //     'apiUrl' => $apiUrl,
        //     'collectionName' => $collectionName,
        //     'settings' => $settings
        // ]));
        $this->apiKey = $apiKey;
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->collectionName = $collectionName;
    }
    
    private function sendRequest(string $method, string $endpoint, array $data = [], array $customHeaders = []): mixed
    {
        // Build full URL (attach query string for GET)
        $url = "{$this->apiUrl}{$endpoint}";
        if ($method === 'GET' && !empty($data)) {
            $url .= '?' . http_build_query($data);
        }
    
        // Default headers + any custom ones
        $headers = array_merge([
            'Content-Type: application/json',
            "X-TYPESENSE-API-KEY: {$this->apiKey}"
        ], $customHeaders);
    
        // Initialize cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Don't let cURL itself fail on HTTP 4xx/5xx; we want to capture the response body
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
    
        // Attach JSON payload on POST/PUT/PATCH
        if (in_array($method, ['POST', 'PUT', 'PATCH'], true) && !empty($data)) {
            $payload = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }
    
        // Execute & gather info
        $responseRaw = curl_exec($ch);
        $statusCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            error_log('cURL error: ' . curl_error($ch));
        }
        curl_close($ch);
    
        // Decode JSON (or fall back to raw if invalid JSON)
        $json = json_decode($responseRaw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $json = ['raw' => $responseRaw];
        }
    
        // Log the decoded response
        // error_log(json_encode($json));
    
        return [
            'result'     => $json,
            'error'      => $statusCode >= 400 ? ($json['message'] ?? $responseRaw) : null,
            'statusCode' => $statusCode,
        ];
    }
    

    public static function getIndex() 
    {        
    }

    public function setSettings(array $settings = []) 
    {
        // error_log('Typesense: setSettings');
        $collectionData = [
            'name' => $this->collectionName,
            'fields' => [
                ['name' => 'post_title' , 'type' => 'string', 'locale' => 'sv'],
                ['name' => 'post_excerpt' , 'type' => 'string', 'locale' => 'sv', 'optional' => true],
                ['name' => 'content' , 'type' => 'string', 'locale' => 'sv', 'optional' => true],
                ['name' => '.*' , 'type' => 'auto', 'locale' => 'sv'],
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
            'query_by' => 'post_title,post_excerpt,content',
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
        // error_log('Typesense: clearObjects');
        $response = $this->sendRequest('DELETE', "/collections/{$this->collectionName}/documents?truncate=true");
        if ($response['error']) {
            error_log(\json_encode($response['error']));
        }
        return $response['result'];
    }

    public function deleteObject(string $objectId) 
    {
        // error_log('Typesense: deleteObject');
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
        // error_log('Typesense: saveObject');
        $data = [
            ...$object, 
            ...[
                'post_title' => html_entity_decode($object['post_title'] ?? ''),
                'post_excerpt' => html_entity_decode($object['post_excerpt'] ?? ''),
                'id' => $object['uuid'],
                'tags' => $object['tags'] ?? [],
                'categories' => $object['categories'] ?? [],
                'thumbnail_alt' => $object['thumbnail_alt'] && \is_string($object['thumbnail_alt']) ? $object['thumbnail_alt'] : '',
            ]
        ];

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
        // error_log('Typesense: getObjects');
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

    public function shouldSplitRecord(): bool {
        return false;
    }
}
