<?php
namespace WJRosa\Firestore;

class Client
{
    /**
     * @var string
     */
    private $apiRoot = 'https://firestore.googleapis.com/v1beta1/';

    /**
     * @var string
     */
    private $project;

    /**
     * @var string
     */
    private $apiKey;

    function __construct(string $project, string $apiKey) {
        $this->project = $project;
        $this->apiKey = $apiKey;
    }

    private function constructUrl(string $method, array $params = null) {
        $params = is_array($params) ? $params : [];
        return (
            $this->apiRoot . 'projects/' . $this->project . '/' .
            'databases/(default)/' . $method . '?key=' . $this->apiKey . '&' . http_build_query($params)
        );
    }

    private function get(string $method, array $params = null) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->constructUrl($method, $params),
            CURLOPT_USERAGENT => 'cURL'
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    private function post(string $method, array $params, $postBody) {
        $encodedBody = json_encode($postBody);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $this->constructUrl($method, $params),
            CURLOPT_HTTPHEADER => array('Content-Type: application/json','Content-Length: ' . strlen($encodedBody)),
            CURLOPT_USERAGENT => 'cURL',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $encodedBody,
            CURLOPT_TIMEOUT => 10
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    private function patch($method, $params, $postBody) {
        $encodedBody = json_encode($postBody);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_HTTPHEADER => array('Content-Type: application/json','Content-Length: ' . strlen($encodedBody)),
            CURLOPT_URL => $this->constructUrl($method, $params),
            CURLOPT_USERAGENT => 'cURL',
            CURLOPT_POSTFIELDS => $encodedBody
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    private function delete($method, $params) {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_URL => $this->constructUrl($method, $params),
            CURLOPT_USERAGENT => 'cURL'
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    /**
     * @param $collectionName
     * @param $documentId
     * @return Document
     */
    public function getDocument($collectionName, $documentId) {
        if ($response = $this->get("documents/$collectionName/$documentId")) {
            return new Document($response);
        }
    }

    /**
     * @param string $collectionName
     * @param string $documentId
     * @param Document $document
     * @param null $documentExists
     * @return mixed
     */
    public function updateDocument(string $collectionName, string $documentId, Document $document, $documentExists = null) {
        $params = [];
        if (!is_null($documentExists)) {
            $params['currentDocument.exists'] = boolval($documentExists);
        }
        return $this->patch("documents/$collectionName/$documentId", $params, $document->toJson());
    }

    /**
     * @param string $collectionName
     * @param string $documentId
     * @return mixed
     */
    public function deleteDocument(string $collectionName, string $documentId) {
        return $this->delete("documents/$collectionName/$documentId", []);
    }

    /**
     * @param string $collectionName
     * @param Document $document
     * @return mixed
     */
    public function addDocument(string $collectionName, Document $document) {
        return $this->post("documents/$collectionName", [], $document->toJson());
    }

    /**
     * @param $collectionName
     * @return \stdClass
     */
    public function getCollection($collectionName, array $params = null) {
        $collection = [];
        if ($response = $this->get("documents/$collectionName", $params)) {
            $collection = \GuzzleHttp\json_decode($response);
        }
        return $collection;
    }

    /**
     * @param array $collection
     * @return mixed
     */
    public function updateCollection(array $collection) {
        $writes = [];
        foreach($collection as $document) {
            $writes[] = [
                'update' => $document
            ];
        }
        $collection = [
            'writes' => $writes
        ];
        return $this->post("documents:commit", [], $collection);
    }

    /**
     * @param string $collectionName
     * @param array $collection
     * @return mixed
     */
    public function addCollection(string $collectionName, array $collection) {
        $collection = [
            'fields' => $collection
        ];
        return $this->post("documents/$collectionName", [], json_encode($collection));
    }
}