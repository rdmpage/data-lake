<?php

// CrossRef search

require_once (dirname(dirname(__FILE__)) . '/lib.php');
require_once (dirname(dirname(__FILE__)) . '/fingerprint.php');
require_once (dirname(dirname(__FILE__)) . '/lcs.php');



//----------------------------------------------------------------------------------------
// Use search API
function crossref_search($citation, $double_check = true, $theshhold = 0.8)
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
			
			if ($double_check)
			{
				// get metadata 
				$query = explode('&', html_entity_decode($result->coins));
				$params = array();
				foreach( $query as $param )
				{
				  list($key, $value) = explode('=', $param);
		  
				  $key = preg_replace('/^\?/', '', urldecode($key));
				  $params[$key][] = trim(urldecode($value));
				}
		
				//print_r($params);
		
				$hit = '';
				if (isset($params['rft.au']))
				{
					$hit = join(",", $params['rft.au']);
				}
		  
				$hit .= ' ' . $params['rft.atitle'][0] 
					. '. ' . $params['rft.jtitle'][0]
					. ' ' . $params['rft.volume'][0]
					. ': ' .  $params['rft.spage'][0];

				$v1 = $citation;
				$v2 = $hit;
		
				//echo "-- $hit\n";
		
				//echo "v1: $v1\n";
				//echo "v2: $v2\n";
		

				$v1 = finger_print($v1);
				$v2 = finger_print($v2);					

				if (($v1 != '') && ($v2 != ''))
				{
					//echo "v1: $v1\n";
					//echo "v2: $v2\n";

					$lcs = new LongestCommonSequence($v1, $v2);
					$d = $lcs->score();

					// echo $d;

					$score = min($d / strlen($v1), $d / strlen($v2));

					//echo "score=$score\n";
			
					if ($score > $theshhold)
					{
			
					}
					else
					{
						unset ($result);
					}
				}
			}			
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
