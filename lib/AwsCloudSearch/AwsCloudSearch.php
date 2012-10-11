<?php

namespace AwsCloudSearch;

/**
 * AWS CloudSearch wrapper class
 *
 * @author Mark Wilson (@mark_wilson)
 */
class AwsCloudSearch
{
    /**
     * Hostname for CloudSearch
     */
    const CLOUDSEARCH_DOMAIN = 'cloudsearch.amazonaws.com';
    /**
     * Protocol used to connect to CloudSearch API
     */
    const PROTOCOL = 'http';

    /**
     * CloudSearch internal limit on document batch size
     */
    const MAXIMUM_SDF_BATCH_SIZE = 5242880; // 5MB
    /**
     * CloudSearch internal limit on single document size
     */
    const MAXIMUM_SDF_SIZE = 1048576; // 1MB

    /**
     * Method date - API version?
     */
    const METHOD_DATE = '2011-02-01';
    /**
     * Search API endpoint path
     */
    const SEARCH_PATH = 'search';
    /**
     * Document batch API endpoint path
     */
    const DOCUMENT_PATH = 'documents/batch';
    /**
     * Search API domain prefix
     */
    const SEARCH_DOMAIN_PREFIX = 'search';
    /**
     * Document API domain prefix
     */
    const DOCUMENT_DOMAIN_PREFIX = 'doc';

    /**
     * @var string CloudSearch 'domain'
     */
    private $domain;
    /**
     * @var string CloudSearch server location
     */
    private $serverLocation;

    /**
     * @var array List of available document methods
     */
    private $availableTypes;

    /**
     * @var array List of pending SDF documents
     */
    private $pendingSdfs;

    /**
     * @var bool Currently in a transaction
     */
    private $inTransaction = false;

    /**
     * @var array searchable fields
     */
    private $searchableFields = array();


    /**
     * Initialise some fundamental class variables
     */
    public function __construct($domain, $serverLocation)
    {
        $this->checkRequirements();

        // initialise an empty array of pending SDF documents
        $this->resetPendingSdfs();

        $this->domain = $domain;
        $this->serverLocation = $serverLocation;
    }

    /**
     * Initialise base search fields
     * Not a required field, but if nothing is set and nothing passed in to
     *  search params[return-fields] then only IDs are returned
     *
     * @param array $searchFields
     */
    public function setReturnFields(Array $searchFields)
    {
        $this->searchableFields = $searchFields;
    }

    /**
     * Start a batch of document transfers rather than sending immediately
     *
     * @throws \Exception
     */
    public function startTransaction()
    {
        if ($this->inTransaction) {
            throw new \Exception('Transaction already initialised');
        }

        // any additions/updates/deletions performed within a transaction will be performed all at once
        // (in batches of MAXIMUM_SDF_SIZE)
        $this->inTransaction = true;
    }

    /**
     * Send the documents stored in the pending transaction
     *
     * @throws \Exception
     */
    public function sendTransaction()
    {
        $this->checkForTransaction();

        // loop through the pending sdfs and send over in batches to document/batch endpoint
        $this->documentBatch($this->pendingSdfs);

        // reset the transaction to complete process
        $this->clearTransaction();
    }

    /**
     * Clear the current transaction
     *
     * @throws \Exception
     */
    public function clearTransaction()
    {
        $this->checkForTransaction();

        $this->resetPendingSdfs();
        $this->inTransaction = false;
    }

    /**
     * Start processing documents
     *
     * @param $documents
     *
     * @return array
     */
    public function processDocuments($documents)
    {
        if (!$this->inTransaction) {
            // send immediately, do not wait for batch
            return $this->documentBatch($documents);
        }

        // add to transaction
        foreach ($documents as $document) {
            $this->pendingSdfs[] = $document;
        }
    }

    /**
     * Document batch API endpoint
     *
     * @param array $sdfs
     *
     * @return array
     */
    public function documentBatch(Array $sdfs)
    {
        $url = $this->buildUrl(self::DOCUMENT_DOMAIN_PREFIX, self::DOCUMENT_PATH);
        $return = $this->doPostRequest($url, $sdfs);
        return new Response\DocumentResponse($return);
    }

    /**
     * Search API endpoint
     *
     * @param string    $term
     * @param array     $params
     *
     * @return array
     */
    public function search($term, Array $params = array())
    {
        $params['q'] = $term;

        if (!isset($params['return-fields']) && isset($this->searchableFields)) {
            $returnFields = $this->searchableFields;
            $params['return-fields'] = implode(',', $this->searchableFields);
        }

        $url = $this->buildUrl(self::SEARCH_DOMAIN_PREFIX, self::SEARCH_PATH);
        $return = $this->doGetRequest($url, $params);

        if (isset($returnFields)) {
            $return['returnFields'] = $returnFields;
        }
        else if (isset($this->searchableFields)) {
            $return['returnFields'] = $this->searchableFields;
        }

        return new Response\SearchResponse($return);
    }

    /**
     * Start the indexing process on CloudSearch
     *
     * @throws \Exception
     */
    public function startIndex()
    {
        // TODO: initialise indexing on CloudSearch
        throw new \Exception('Index initialisation is not yet implemented');
    }

    /**
     * Perform a get request
     *
     * @param string    $url
     * @param array     $params
     *
     * @return array
     */
    private function doGetRequest($url, Array $params)
    {
        $url .= '?' . http_build_query($params);
        return $this->doRequest($url, 'GET');
    }

    /**
     * Perform a post request
     *
     * @param string    $url
     * @param array     $params
     *
     * @return array
     */
    private function doPostRequest($url, $params)
    {
        return $this->doRequest($url, 'POST', $params);
    }

    /**
     * Connect to Amazon CloudSearch with specified parameters
     *
     * @param string    $url
     * @param string    $method
     * @param array     $parameters
     *
     * @throws \Exception
     *
     * @return array        contains successful status, http code, and response data
     */
    private function doRequest($url, $method, Array $parameters = null)
    {
        // check required parameters are set when posting
        if ($method == 'POST' && is_null($parameters)) {
            throw new \Exception('Parameters must be defined when sending a post request');
        }

        $curl = curl_init();

        $additionalHeaders = array(
            'Accept: application/json'
        );

        if ($method == 'POST') {
            $encodedParameters = array();
            foreach ($parameters as $parameter) {
                $encodedParameters[] = (String) $parameter;
            }
            $encodedParameters = '[' . implode(',', $encodedParameters) . ']';

            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $encodedParameters);

            $additionalHeaders[] = 'Content-Type: application/json';
            $additionalHeaders[] = 'Content-Length: ' . strlen($encodedParameters);
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $additionalHeaders);

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  
        $response = curl_exec($curl);
        $responseInfo = curl_getinfo($curl);
        curl_close($curl);

        // build return array
        $result = array(
            'response' => $response,
            'info' => $responseInfo
        );

        return $result;
    }

    /**
     * Get array of available communications to document/batch endpoint
     *
     * @return array
     */
    public function getAvailableTypes()
    {
        return $this->availableTypes;
    }


    /**
     * Check AWS CloudSearch wrapper class requirements
     *
     * @throws \Exception
     */
    private function checkRequirements()
    {
        if (!extension_loaded('curl')) {
            throw new \RuntimeException('Curl extension must be loaded');
        }
    }

    /**
     * Reset pending documents - used in initialisation and after a transaction
     */
    private function resetPendingSdfs()
    {
        $this->pendingSdfs = array();
    }

    /**
     * Check if a transaction has been started
     *
     * @throws \Exception
     */
    private function checkForTransaction()
    {
        if (!$this->inTransaction) {
            throw new \Exception('Transaction has not been started');
        }
    }

    /**
     * Build CloudSearch URL from constructor variables and constants
     *
     * @param string    $domainPrefix
     * @param string    $urlPath
     *
     * @return string
     */
    private function buildUrl($domainPrefix, $urlPath)
    {
        $url = self::PROTOCOL . '://' . $domainPrefix . '-'
            . $this->domain . '.' . $this->serverLocation . '.'
            . self::CLOUDSEARCH_DOMAIN . '/'
            . self::METHOD_DATE . '/' . $urlPath;

        return $url;
    }
}    
