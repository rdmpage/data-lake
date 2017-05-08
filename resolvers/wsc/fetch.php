<?php

// World Spider Catalogue LSID

require_once(dirname(dirname(__FILE__)) . '/lib.php');

//----------------------------------------------------------------------------------------
function wsc_fetch($lsid)
{
	$data = null;
	
	$url = 'http://lsid.nmbe.ch:80/authority/metadata/?lsid=' . $lsid;

	$rdf = get($url);
	
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
	$lsid = 'urn:lsid:nmbe.ch:spidersp:047725';
		
	$data = wsc_fetch($lsid);
	
	print_r($data);
}

?>
