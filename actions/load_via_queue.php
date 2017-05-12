<?php

// Load some data

require_once(dirname(dirname(__FILE__)) . '/queue/queue.php');

// URLs we want to load
$urls=array('http://dx.doi.org/10.7554/eLife.08347');

$urls = array('http://orcid.org/0000-0002-0235-9506');
$urls = array('http://dx.doi.org/10.3732/ajb.1000283');

$urls = array('http://dx.doi.org/10.3897/zookeys.647.11192');

$urls = array('http://dx.doi.org/10.3897/phytokeys.54.3285', 'http://orcid.org/0000-0001-9892-0355');

// IPNI name, ORCID for author 
$urls=array('urn:lsid:ipni.org:names:77096652-1', 'http://orcid.org/0000-0003-0813-6650');

$urls = array('http://dx.doi.org/10.1007/s12225-010-9229-9'); // Kew Bulletin article with unstructured citations

$urls=array('urn:lsid:ipni.org:names:60447735-2');

// genus Cypringlea
$urls=array(
// names
'urn:lsid:ipni.org:names:60447735-2',
'urn:lsid:ipni.org:names:20011928-1',
'urn:lsid:ipni.org:names:326724-2',
'urn:lsid:ipni.org:names:20011929-1',
'urn:lsid:ipni.org:names:20011930-1',
'urn:lsid:ipni.org:names:1020928-2',
'urn:lsid:ipni.org:names:1012364-2',

// basionyms
'urn:lsid:ipni.org:names:284742-2',
'urn:lsid:ipni.org:names:313164-1',
'urn:lsid:ipni.org:names:313398-1',
'urn:lsid:ipni.org:names:230281-2',
'urn:lsid:ipni.org:names:230193-2',

// invalid
'urn:lsid:ipni.org:names:230486-2'
);

// genus Cypringlea papers
$urls = array(
'http://dx.doi.org/10.21829/abm83.2008.1057',
'http://dx.doi.org/10.2307/3393577',
'http://dx.doi.org/10.2307/2399609',
'http://dx.doi.org/10.2307/2804749'
// 'http://www/jstor.org/stable/43781109


);

// CiNii
$urls = array(
'http://ci.nii.ac.jp/naid/110003758633#article'
);

$urls = array(
'http://ci.nii.ac.jp/naid/110003352291',
'http://ci.nii.ac.jp/naid/130000017049'
);

// J-Stage DOI
$urls = array(
'http://doi.org/10.18942/apg.201615'
);


// The Cucurbitaceae of India: Accepted names, synonyms, geographic distribution, and information on images and DNA sequences
// massive paper with lots of references (BUT these aren't in CrossRef metadata!!)
//$urls = array('http://dx.doi.org/10.3897/phytokeys.20.3948');

// ZooBank publication via LSID
$urls = array(
'urn:lsid:zoobank.org:pub:F1DE2C0F-1C90-468E-856B-4A0BCEC56A07'
);


// ZooBank, DOI, ORCID example 

$urls = array(
'urn:lsid:zoobank.org:pub:0FB0955D-5FAF-47C4-A2BE-114E1DC7D997',
'http://orcid.org/0000-0003-1732-9155',
'http://orcid.org/0000-0002-7263-6505',
'http://orcid.org/0000-0002-7210-1033'
);

// ZooBank publication via LSID
$urls = array(
'urn:lsid:zoobank.org:pub:53A0DD28-F41B-493D-915D-A89404E349D7'
);

$urls = array(
'urn:lsid:nmbe.ch:spidersp:047725'
);

// Spider taxonomist in ORCID
$urls = array(
'http://orcid.org/0000-0002-8186-8316'
);

$urls = array(
'urn:lsid:zoobank.org:pub:79A6393D-8021-41B8-BF1A-2A3723AFECFB'
);

// Index Fungorum example with basionym, publication, and orcids
// need to automatically add IF basionyms to queue
// ORCIDs for these authors have LOTs of references so best suppress those
// need glue to connect IF LSID to DOI for paper
$urls = array(
'urn:lsid:indexfungorum.org:names:813327',
'urn:lsid:indexfungorum.org:names:813436',
'http://orcid.org/0000-0002-1072-5166',
'http://orcid.org/0000-0002-5144-6200',
'http://dx.doi.org/10.1016/j.simyco.2015.12.002'
);

// revisied ORCID model, each document is separate
$urls=array(
'http://orcid.org/0000-0003-0566-372X'
);

$urls = array('http://dx.doi.org/10.1007/s12225-010-9229-9'); // Kew Bulletin article with unstructured citations


// ORCID with some references not linked to DOIs
// Some references, such as Novon article http://biostor.org/reference/64541,
// are in BioStor, so we can link ORCIDs to BioStor and BHL
$urls = array(
'http://orcid.org/0000-0002-8758-9326'
);

// J. J. Wieringa
$urls = array(
'http://orcid.org/0000-0003-0566-372X'
);

// CiNii
$urls = array(
'http://ci.nii.ac.jp/naid/110004661805#article'
);

$force = false;
$force = true;

// Add items to the queue
foreach ($urls as $url)
{
	echo $url . "\n";
	enqueue($url, $force);
}
	
// Resolve items
while (!queue_is_empty())
{	
	dequeue(100);
}

?>