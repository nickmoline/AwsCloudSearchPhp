<?php

namespace AwsCloudSearch\Document;

abstract class AbstractDocument
{
    protected $id;
    protected $type;
    protected $version;
    protected $lang;

    public function __construct($method, $id, $revision, $lang = 'en')
    {
        $this->id = $id;
        $this->type = $method;
        $this->version = $revision;
        $this->lang = $lang;
    }

    public function __toString()
    {
        return $this->convertToSdf();
    }

    protected function convertToSdf()
    {
        return json_encode(get_object_vars($this));
    }
}