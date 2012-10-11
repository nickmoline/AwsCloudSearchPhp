<?php

namespace AwsCloudSearch\Response;

abstract class AbstractResponse
{
    private $rawResponse;
    private $rawResponseInfo;

    protected $parsedData;

    protected $errors;

    public function __construct(Array $data)
    {
        if (!isset($data['response'])) {
            throw new \Exception('Invalid response');
        }
        if (!isset($data['info'])) {
            throw new \Exception('Invalid response info');
        }

        $this->rawResponse = $data['response'];
        $this->rawResponseInfo = $data['info'];

        $this->parseResponse();
    }

    public function parseResponse()
    {
        $parsedData = json_decode($this->rawResponse);
        $this->parsedData = $parsedData;

        if (isset($this->parsedData->errors)) {
            $errors = $this->parsedData->errors;
            $this->errors = $errors;
        }
    }

    public function getHttpCode()
    {
        return $this->rawResponseInfo['http_code'];
    }

    public function wasSuccessful()
    {
        return ($this->getHttpCode() >= 200 && $this->getHttpCode() < 300);
    }

    public abstract function __toString();

    protected function getRawResponse()
    {
        return $this->rawResponse;
    }

    protected function getRawResponseInfo()
    {
        return $this->rawResponseInfo;
    }
}