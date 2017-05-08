<?php

// ION LSID

require_once(dirname(dirname(__FILE__)) . '/lib.php');

//----------------------------------------------------------------------------------------
function ion_fetch($lsid)
{
	$data = null;
	
	$id = str_replace('urn:lsid:organismnames.com:name:', '', $lsid);
	
	$url = 'http://www.organismnames.com/lsidmetadata.htm?lsid=' . $id;
	
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
	$lsid = 'urn:lsid:organismnames.com:name:5212418';
		
	$data = ion_fetch($lsid);
	
	print_r($data);
}

?>
