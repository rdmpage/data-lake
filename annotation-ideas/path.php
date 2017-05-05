<?php

// Extract path to taxonomic treatment in an XML document

$filename = 'xml.xml'; // Phytokeys Pensoft
$filename = 'phytokeys-1426.xml';
//$filename = 'Nota Lepidopterologica.xml'; // Nota Lepidopterologica
//$filename = '5039.xml'; // BDJ

$paths = array();

$xml = file_get_contents($filename);

$dom= new DOMDocument;
$dom->loadXML($xml);
$xpath = new DOMXPath($dom);

// get taxonomic treatments

$nodeCollection = $xpath->query ('//tp:taxon-treatment');

foreach ($nodeCollection as $node)
{
	// echo $node->getNodePath() . "\n";
	
	$path =  $node->getNodePath();
	$paths[$path] = array();
	
	// locate identifier
	$nc = $xpath->query ('tp:nomenclature/tp:taxon-name/object-id', $node);
	foreach ($nc as $n)
	{
		$identifier = $n->firstChild->nodeValue;
		$paths[$path][] = $identifier;
	}
}


print_r($paths);


?>
