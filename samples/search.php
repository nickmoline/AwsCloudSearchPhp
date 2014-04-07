<?php

// Reflects your search domain
const SEARCH_DOMAIN = '<your-search-domain>';

// Reflects your server location (e.g., 'us-west-2')
const SERVER_LOCATION = '<your-server-location>';

// Some search term
const SEARCH_QUERY = 'king';

$loader = require 'vendor/autoload.php';
$cloudSearch = new \AwsCloudSearch\AwsCloudSearch(SEARCH_DOMAIN, SERVER_LOCATION);

// Here, you can fill in return fields (if you like)
$cloudSearch->setReturnFields(array('title', 'plot', '_score'));

// search domain's indexed documents, including some additional parameters
$response = $cloudSearch->search(SEARCH_QUERY, array('start' => 0, 'size' => 10));
if ($response->wasSuccessful()) 
{
    print_r($response->getHitDocuments());
}
else 
{
	print_r($response->getErrors());
}

?>

