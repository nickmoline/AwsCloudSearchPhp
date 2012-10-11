<?php

namespace AwsCloudSearch\Document;

class DeleteDocument extends AbstractDocument
{
    public function __construct($id, $revision)
    {
        parent::__construct('delete', $id, $revision);
    }
}