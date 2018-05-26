<?php
namespace WJRosa\Firestore;

class Client
{
    /**
     * @var string
     */
    private $project;

    private $service;

    function __construct(string $project) {
        $scopes = ['https://www.googleapis.com/auth/datastore'];
        $client = new \Google_Client();
        $client->addScope($scopes);
        $client->useApplicationDefaultCredentials();
        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithAssertion();
        }
        $firestoreService = new \Google_Service_Firestore($client);
        $databaseService = $firestoreService->projects_databases_documents;
        $this->service = $databaseService;
        $this->project = $project;
    }

    private function constructUrl(string $method, array $params = null) {
        $params = is_array($params) ? $params : [];
        return 'projects/' . $this->project . '/databases/(default)/' . $method . '?' . http_build_query($params);
    }

    private function get(string $method, array $params = null) {
        return $this->service->get($this->constructUrl($method, $params));
    }

    private function patch($method, $params, \Google_Service_Firestore_Document $document) {
        return $this->service->patch($this->constructUrl($method, $params), $document);
    }

    private function delete($method, $params) {
        return $this->service->delete($this->constructUrl($method, $params));
    }

    /**
     * @param $collectionName
     * @param $documentId
     * @return \Google_Service_Firestore_Document
     */
    public function getDocument($collectionName, $documentId) {
        if ($response = $this->get("documents/$collectionName/$documentId")) {
            return $response;
        }
        return new \Google_Service_Firestore_Document();
    }

    /**
     * @param string $collectionName
     * @param string $documentId
     * @param \Google_Service_Firestore_Document $document
     * @param null $documentExists
     * @return mixed
     */
    public function updateDocument(string $collectionName, string $documentId, \Google_Service_Firestore_Document $document, $documentExists = null) {
        $params = [];
        if (!is_null($documentExists)) {
            $params['currentDocument.exists'] = boolval($documentExists);
        }
        return $this->patch("documents/$collectionName/$documentId", $params, $document);
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
     * @param \Google_Service_Firestore_Document $document
     * @return mixed
     */
    public function addDocument(string $collectionName, \Google_Service_Firestore_Document $document) {
        return $this->service->createDocument("documents/$collectionName", $collectionName, $document);
    }

    /**
     * @param $collectionName
     * @param $params
     * @return \Google_Service_Firestore_Document
     */
    public function getCollection($collectionName, array $params = null) {
        if ($response = $this->get("documents/$collectionName", $params)) {
            return $response;
        }
        return new \Google_Service_Firestore_Document();
    }

    /**
     * @param \Google_Service_Firestore_Document $collection
     * @return \Google_Service_Firestore_CommitResponse|mixed
     */
    public function updateCollection(array $collection) {
        $writes = [];
        foreach($collection as $document) {
            $writes[] = [
                'update' => $document
            ];
        }
        $writeRequest = new \Google_Service_Firestore_CommitRequest();
        $writeRequest->setWrites($writes);
        return $this->service->commit('projects/' . $this->project . '/databases/(default)', $writeRequest);
    }

    /**
     * @param string $collectionName
     * @param \Google_Service_Firestore_Document $collection
     * @return mixed
     */
    public function addCollection(string $collectionName, \Google_Service_Firestore_Document $collection){
        $collection = [
            'fields' => $collection
        ];

        return $this->service->createDocument("documents", $collectionName, $collection);
    }
}