<?php

namespace AwsCloudSearch\Response;

class SuggestResponse extends AbstractResponse
{
    private $suggestions;

	/**
	 * Constructor
	 */
    public function __construct(Array $data)
    {
        parent::__construct($data);
    }

	/**
	 * Parse the response from AWS
	 */
    public function parseResponse()
    {
        parent::parseResponse();

        if (!$this->wasSuccessful()) {
            return;
        }

		// Get actual suggestions as an array
        $suggestions = $this->parsedData->suggest->suggestions;

        if (0 === count($suggestions)) {
        	// No suggestions...
            $this->suggestions = null;
            return;
        }
/*
        $returnSuggestions = array();
        foreach ($suggestions as $suggestion) {
            if (isset($suggestion->suggestion)) {
                $returnSuggestions[$suggestion->id] = $suggestion->suggestion;
            }
        }
*/
        $this->suggestions = $suggestions;
    }

	/**
	 * Get the suggestions
	 */
    public function getSuggestions()
    {
        if (!$this->wasSuccessful()) {
            throw new \Exception('Unsuccessful search cannot return an array');
        }

        return $this->suggestions;
    }

	/**
	 * Get errors that occurred
	 */
    public function getErrors()
    {
        if ($this->wasSuccessful()) {
            throw new \Exception('No errors in response');
        }

        return $this->parsedData->errors;
    }

	/**
	 * String representation of suggestions
	 */
    public function __toString()
    {
        if ($this->wasSuccessful()) {
            $returnString = '';

            foreach ($this->parsedData->suggest->suggestions as $suggestion) {
                $returnString .= $suggestion->suggestion . ', ';
            }
            $returnString = rtrim($returnString, ', ');
            return $returnString;
        }
        else {
            return 'No suggest results';
        }
    }
}

?>

