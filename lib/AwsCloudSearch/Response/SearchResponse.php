<?php

namespace AwsCloudSearch\Response;

class SearchResponse extends AbstractResponse
{
    private $searchedFields;

    private $hits;
    public $props;

    public function __construct(Array $data)
    {
        parent::__construct($data);

        if (isset($data['returnFields'])) {
            $this->searchedFields = $data['returnFields'];
        }
        else {
            $this->searchedFields = array('id');
        }
    }

    public function parseResponse()
    {
        parent::parseResponse();

        if (!$this->wasSuccessful()) {
            return;
        }

        $hits = $this->parsedData->hits->hit;
	$this->props = $this->parsedData->hits;
	$this->cursor = $this->parsedData->hits->cursor;
        if (0 === count($hits)) {
            $this->hits = null;
            return;
        }
        
		/* Not sure what this is supposed to do
		 *
        $returnHits = array();
        foreach ($hits as $hit) {
            if (isset($hit->data)) {
                $returnHits[$hit->id] = $hit->data;
            }
            else {
                $returnHits[$hit->id] = $hit->id;
            }
        }

        foreach ($returnHits as $key => $hit) {
            if (is_object($hit)) {
                foreach ($hit as $dataKey => $data) {
                    $returnHits[$key]->{$dataKey} = (!empty($data) ? $data[0] : null);
                }
            }
        }
        $this->hits = $returnHits;
		*/

		$this->hits = $hits;
    }

    public function getHitDocuments()
    {
        if (!$this->wasSuccessful()) {
            throw new \Exception('Unsuccessful search can not return an array');
        }

        return $this->hits;
    }

    public function getErrors()
    {
        if ($this->wasSuccessful()) {
            throw new \Exception('No errors in response');
        }

        return $this->parsedData->errors;
    }

    public function __toString()
    {
        if ($this->wasSuccessful()) {
            $returnString = '';

            $searchField = $this->searchedFields[0];

            foreach ($this->parsedData->hits->hit as $hit) {
                $returnString .= $hit->data->{$searchField} . ', ';
            }
            $returnString = rtrim($returnString, ', ');
            return $returnString;
        }
        else {
            return 'No search results';
        }
    }
}
