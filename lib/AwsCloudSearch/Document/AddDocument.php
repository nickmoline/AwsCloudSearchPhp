<?php

namespace AwsCloudSearch\Document;

class AddDocument extends AbstractDocument
{
    protected $fields;

    public function __construct($id, $revision, $lang = 'en', Array $fields = array())
    {
        parent::__construct('add', $id, $revision, $lang);

        $this->fields = (object) $fields;
    }
}