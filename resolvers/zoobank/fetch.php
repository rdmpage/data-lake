<?php

// Zoobank API

require_once (dirname(dirname(__FILE__)) . '/lib.php');
require_once (dirname(dirname(__FILE__)) . '/shared/crossref.php');


//----------------------------------------------------------------------------------------
function zoobank_fetch_pub($uuid, $lookup_works = true)
{
	$data = null;
	
	$url = 'http://zoobank.org/References.json/' . $uuid;
	
	$json = get($url);
	
	if ($json != '')
	{
		$obj = json_decode($json);
		if ($obj)
		{
			$data = new stdclass;
			
			$data->{'message-format'} = 'application/vnd.zoobank+json'; // I made this up
			
			$data->{'message-timestamp'} = date("c", time());
			$data->{'message-modified'}  = $data->{'message-timestamp'};
			
			$data->message = $obj[0];
			
			// Look for DOI
			if ($lookup_works)
			{
				$citation = $data->message->label;
				
				echo "Looking up \"$citation\"... ";
				$result = crossref_search($citation);
				if (isset($result->doi))
				{
					echo $result->doi;
					$data->message->doi = $result->doi;
					
					$data->links[] = 'http://dx.doi.org/' . $result->doi;
				}
				echo "\n";
			}
					
						
			
		}
	}

	return $data;
}


if (0)
{
	$uuid = 'F1DE2C0F-1C90-468E-856B-4A0BCEC56A07';

	$data = zoobank_fetch_pub($uuid);
	
	print_r($data);
}


?>
