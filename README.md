# AwsCloudSearch PHP API Wrapper

A library to interact with Amazon's AWS CloudSearch API. Please note that this is a fork of Sander Huijsen's fork of Mark Wilson's AwsCloudSearchPhp. You might want to use one of their libraries in stead (see Credits).

## Requirements
* PHP 5+
* cURL extension
* AWS account
* CloudSearch domain

## Getting Started
For detailed instructions, check out [Making Amazon CloudSearch API Requests](http://docs.amazonwebservices.com/cloudsearch/latest/developerguide/APIReq.html).

    curl -s https://getcomposer.org/installer | php
    php composer.phar install

Require "raptor404/awscloudsearchphp" in your composer.json:-

    {
        "require": {
            "raptor404/awscloudsearchphp": "dev-master"
        }
    }

Pass your CloudSearch domain and server location to initialise the class:

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

You can also check out the samples/ directory.

Documents must be formatted to the [Search Data Format (SDF)](http://docs.amazonwebservices.com/cloudsearch/latest/developerguide/GettingStartedSendData.html).

## Credits
Forked from [AwsCloudSearchPhp](https://github.com/sandyman/AwsCloudSearchPhp) which is a for of [AwsCloudSearchPhp](https://github.com/markwilson/AwsCloudSearchPhp).
