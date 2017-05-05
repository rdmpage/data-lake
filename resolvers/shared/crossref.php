<?php

// CrossRef search

require_once (dirname(dirname(__FILE__)) . '/lib.php');


//----------------------------------------------------------------------------------------
// Use search API
function crossref_search($citation)
{
	global $config;
	
	$result = null;
		
	$post_data = array();
	$post_data[] = $citation;
		
	$ch = curl_init(); 
	
	$url = 'http://search.crossref.org/links';
	
	curl_setopt ($ch, CURLOPT_URL, $url); 
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 

	// Set HTTP headers
	$headers = array();
	$headers[] = 'Content-type: application/json'; // we are sending JSON
	
	// Override Expect: 100-continue header (may cause problems with HTTP proxies
	// http://the-stickman.com/web-development/php-and-curl-disabling-100-continue-header/
	$headers[] = 'Expect:'; 
	curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);
	
	if ($config['proxy_name'] != '')
	{
		curl_setopt($ch, CURLOPT_PROXY, $config['proxy_name'] . ':' . $config['proxy_port']);
	}

	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
	
	$response = curl_exec($ch);
	
	$obj = json_decode($response);
	if (count($obj->results) == 1)
	{
		if ($obj->results[0]->match)
		{
			$obj->results[0]->doi = str_replace('http://dx.doi.org/', '', $obj->results[0]->doi);
			$result = $obj->results[0];
		}
	}
	
	return $result;
	
}

if (0)
{
	$citation = 'Moorea BIOCODE barcode library as a tool for understanding predator-prey interactions: insights into the diet of common predatory coral reef fishes. Coral Reefs 31 (2), 383-388 (2012)';

	$data = crossref_search($citation);
	print_r($data);
}


?>
