<?php

namespace AwsCloudSearch\Response;

class DocumentResponse extends AbstractResponse
{
    public function getErrors()
    {
        if ($this->wasSuccessful()) {
            throw new \Exception('No errors in response');
        }

        return $this->parsedData->errors;
    }

    public function __toString()
    {
        return sprintf('Status: %s, additions: %s, deletions: %s', $this->parsedData->status, $this->parsedData->adds, $this->parsedData->deletes);
    }
}