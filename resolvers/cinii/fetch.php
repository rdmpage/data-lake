<?php

// CiNii RDF

require_once(dirname(dirname(__FILE__)) . '/lib.php');


//----------------------------------------------------------------------------------------
function cinii_fetch($id)
{
	$data = null;
	
	$url = 'http://ci.nii.ac.jp/naid/' . $id . '.rdf';
		
	echo "-- $url\n";

	$rdf = get($url, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) AppleWebKit/600.5.17 (KHTML, like Gecko) Version/8.0.5 Safari/600.5.17');
	
	if ($rdf != '')
	{
		$data = new stdclass;		
		$data->{'message-format'} = 'application/rdf+xml';
					
		$data->message = new stdclass;
		$data->message->xml = $rdf;	
	}
	return $data;
}


// test cases

if (0)
{
		
	$data = cinii_fetch(110003758633);
	
	print_r($data);
}

?>
