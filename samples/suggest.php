<?php

// Reflects your search domain
const SEARCH_DOMAIN = '<your-search-domain>';

// Reflects your server location (e.g., 'us-west-2')
const SERVER_LOCATION = '<your-server-location>';

// Some term to use for suggestions
const SUGGEST_QUERY = 'king';

// Suggester to use (set up in AWS CloudSearch dashboard)
const SUGGESTER = '<your-suggester>';

$loader = require 'vendor/autoload.php';
$cloudSearch = new \AwsCloudSearch\AwsCloudSearch(SEARCH_DOMAIN, SERVER_LOCATION);

// search domain's indexed documents
$response = $cloudSearch->suggest(SUGGEST_QUERY, SUGGESTER);
if ($response->wasSuccessful()) 
{
    print_r($response->getSuggestions());
}
else 
{
	print_r($response->getErrors());
}

?>

