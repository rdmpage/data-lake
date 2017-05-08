<?php

// Index Fungorum LSID

require_once(dirname(dirname(__FILE__)) . '/lib.php');

//----------------------------------------------------------------------------------------
function indexfungorum_fetch($lsid)
{
	$data = null;
	
	$url = 'http://www.indexfungorum.org/IXFWebService/Fungus.asmx/NameByKeyRDF?NameLsid=' . $lsid;
	
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
	$lsid = 'urn:lsid:indexfungorum.org:names:813327';
		
	$data = indexfungorum_fetch($lsid);
	
	print_r($data);
}

?>
