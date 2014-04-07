# AwsCloudSearch PHP API Wrapper

A library to interact with Amazon's AWS CloudSearch API.

## Requirements
* PHP 5+
* cURL extension
* AWS account
* CloudSearch domain

## Getting Started
For detailed instructions, check out [Making Amazon CloudSearch API Requests](http://docs.amazonwebservices.com/cloudsearch/latest/developerguide/APIReq.html).

    curl -s https://getcomposer.org/installer | php
    php composer.phar install

Require "sandyman/awscloudsearchphp" in your composer.json:-

    {
        "require": {
            "sandyman/awscloudsearchphp": "dev-master"
        }
    }

Pass your CloudSearch domain and server location to initialise the class:-

    $loader = require 'vendor/autoload.php';
    $cloudSearch = new \AwsCloudSearch\AwsCloudSearch(<domain>, <server location>);

    // search domain's indexed documents
    $cloudSearch->setReturnFields(<array of fields in storage>);
    $response = $cloudSearch->search(<term>, <additional parameters>);
    if ($response->wasSuccessful()) {
        print_r($response->getHitDocuments());
    }
    else {
        print_r($response->getErrors());
    }

    // add/update/delete array of documents
    $documents = array();
    $document = new \AwsCloudSearch\Document\AddDocument(<id>, <version>, <lang=en>, <fields array>);
    $documents[] = $document;
    
    $response = $cloudSearch->processDocuments($documents);
    if ($response->wasSuccesful()) {
        echo $response;
    }
    else {
        print_r($response->getErrors());
    }

Documents must be formatted to the [Search Data Format (SDF)](http://docs.amazonwebservices.com/cloudsearch/latest/developerguide/GettingStartedSendData.html).

## To Do
 * Error handling
 * Initialise CloudSearch indexing
 * Complete Response classes
 * PHPDoc blocks
 * Send documents in blocks rather than attempting all at once
 * Handle HTML error response - e.g. 403 Forbidden
 * Improve search and rank configuration - move to an object rather array

## Getting Help
If you need help or have questions, please contact [Sander Huijsen](http://twitter.com/ahuijsen).

## Credits
Forked from [AwsCloudSearchPhp](https://github.com/markwilson/AwsCloudSearchPhp).
