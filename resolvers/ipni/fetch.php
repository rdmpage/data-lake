<?php

// IPNI LSID

require_once(dirname(dirname(__FILE__)) . '/lib.php');


//----------------------------------------------------------------------------------------
function ipni_fetch($lsid)
{
	$data = null;
	
	$url = 'http://ipni.org/' . $lsid;
		
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
	$lsid = 'urn:lsid:ipni.org:names:77096652-1';
		
	$data = ipni_fetch($lsid);
	
	print_r($data);
}

?>
