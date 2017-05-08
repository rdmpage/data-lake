<?php

// Resolve one object
require_once(dirname(dirname(__FILE__)) . '/documentstore/couchsimple.php');

//require_once (dirname(__FILE__) . '/bhl/fetch.php');
//require_once (dirname(__FILE__) . '/bold/fetch.php');
require_once (dirname(__FILE__) . '/cinii/fetch.php');
require_once (dirname(__FILE__) . '/crossref/fetch.php');
//require_once (dirname(__FILE__) . '/gbif/fetch.php');
//require_once (dirname(__FILE__) . '/genbank/fetch.php');
require_once (dirname(__FILE__) . '/indexfungorum/fetch.php');
require_once (dirname(__FILE__) . '/ion/fetch.php');
require_once (dirname(__FILE__) . '/ipni/fetch.php');
require_once (dirname(__FILE__) . '/orcid/fetch.php');
//require_once (dirname(__FILE__) . '/pubmed/fetch.php');
//require_once (dirname(__FILE__) . '/worldcat/fetch.php');
require_once (dirname(__FILE__) . '/wsc/fetch.php');
require_once (dirname(__FILE__) . '/zoobank/fetch.php');

//----------------------------------------------------------------------------------------
// Classify URL link
function classify_url($url)
{
	$identifier = null;
	
	// BHL
	if (preg_match('/http[s]?:\/\/(www\.)?biodiversitylibrary.org\/page\/(?<id>.*)$/', $url, $m))
	{
		$identifier = new stdclass;
		$identifier->namespace = 'BHL_PAGE';
		$identifier->id = $m['id'];	
	}
	
	// BOLD
	if (preg_match('/http[s]?:\/\/bins.boldsystems.org\/index.php\/Public_RecordView\?processid=(?<id>.*)$/', $url, $m))
	{
		$identifier = new stdclass;
		$identifier->namespace = 'BOLD';
		$identifier->id = $m['id'];
	}
	
	// CiNii http://ci.nii.ac.jp/naid/110003758633
	if (preg_match('/http[s]?:\/\/ci.nii.ac.jp\/naid\/(?<id>\d+)/', $url, $m))
	{
		$identifier = new stdclass;
		$identifier->namespace = 'CINII';
		$identifier->id = $m['id'];
	}	
		
	// DOI
	if (preg_match('/http[s]?:\/\/(dx.)?doi.org\/(?<doi>.*)$/', $url, $m))
	{
		$identifier = new stdclass;
		$identifier->namespace = 'DOI';
		$identifier->id = $m['doi'];
	}

	// GBIF Occurrence
	if (preg_match('/http[s]?:\/\/(www\.)?gbif.org\/occurrence\/(?<id>\d+)$/', $url, $m))
	{
		$identifier = new stdclass;
		$identifier->namespace = 'GBIF_OCCURRENCE';
		$identifier->id = $m['id'];
	}

	// GBIF species
	if (preg_match('/http[s]?:\/\/(www\.)?gbif.org\/species\/(?<id>\d+)$/', $url, $m))
	{
		$identifier = new stdclass;
		$identifier->namespace = 'GBIF_SPECIES';
		$identifier->id = $m['id'];
	}
	
	
	// Index Fungorum name
	if (preg_match('/urn:lsid:indexfungorum.org:names/', $url))
	{
		$identifier = new stdclass;
		$identifier->namespace = 'INDEX_FUNGORUM_NAME';
		$identifier->id = $url;	
	}
	
	// ION name
	if (preg_match('/urn:lsid:organismnames.com:name/', $url))
	{
		$identifier = new stdclass;
		$identifier->namespace = 'ION_NAME';
		$identifier->id = $url;	
	}
	
	// IPNI name
	if (preg_match('/urn:lsid:ipni.org:names/', $url))
	{
		$identifier = new stdclass;
		$identifier->namespace = 'IPNI_NAME';
		$identifier->id = $url;	
	}
	
	// ORCID
	if (preg_match('/http[s]?:\/\/orcid.org\/(?<orcid>([0-9]{4})(-[0-9A-Z]{4}){3})$/i', $url, $m))
	{
		$identifier = new stdclass;
		$identifier->namespace = 'ORCID';
		$identifier->id =  $m['orcid'];
	}
	
	// ISSN (WorldCat)
	if (preg_match('/http[s]?:\/\/www.worldcat.org\/issn\/(?<issn>[0-9]{4}-[0-9]{3}([0-9]|X))$/', $url, $m))
	{
		$identifier = new stdclass;
		$identifier->namespace = 'ISSN';
		$identifier->id = $m['issn'];
	}	

	// NCBI GenBank
	if (preg_match('/http[s]?:\/\/www.ncbi.nlm.nih.gov\/nuccore\/(?<id>.*)$/', $url, $m))
	{
		$identifier = new stdclass;
		$identifier->namespace = 'GENBANK';
		$identifier->id = $m['id'];
	}
	
	// PubMed PMID
	if (preg_match('/http[s]?:\/\/www.ncbi.nlm.nih.gov\/pubmed\/(?<pmid>\d+)$/', $url, $m))
	{
		$identifier = new stdclass;
		$identifier->namespace = 'PMID';
		$identifier->id = $m['pmid'];
	}
	
	// World Spider Catalogue
	
	if (preg_match('/urn:lsid:nmbe.ch:spidersp/', $url))
	{
		$identifier = new stdclass;
		$identifier->namespace = 'WSC_NAME';
		$identifier->id = $url;	
	}
	
	
	// ZooBank reference
	if (preg_match('/urn:lsid:zoobank.org:pub:(?<uuid>.*)$/', $url, $m))
	{
		$identifier = new stdclass;
		$identifier->namespace = 'ZOOBANK_PUB';
		$identifier->id = $m['uuid'];
	}
	
	
	return $identifier;
}
	
	
//----------------------------------------------------------------------------------------
function resolve_url($url)
{
	$data = null;
	
	$identifier = classify_url($url);
	
	if (0)
	{
		echo $url . "\n";
		print_r($identifier);
	}

	if ($identifier)
	{	
		switch ($identifier->namespace)
		{	
		/*
			case 'BHL_PAGE':
				$data = bhl_page_fetch($identifier->id);
				break;
		
			case 'BOLD':
				$data = barcode_fetch($identifier->id);
				break;
		*/
			case 'CINII':
				$data = cinii_fetch($identifier->id);
				break;		
		
			case 'DOI':
				$data = crossref_fetch($identifier->id);
				break;
		/*		
			case 'GBIF_OCCURRENCE':
				$data = gbif_fetch_occurrence($identifier->id);
				break;

			case 'GBIF_SPECIES':
				$data = gbif_fetch_species($identifier->id);
				break;

			case 'GENBANK':
				$data = genbank_fetch($identifier->id);
				break;
				
			case 'ISSN':
				$data = worldcat_fetch($identifier->id);
				break;
			*/
			
			case 'INDEX_FUNGORUM_NAME':
				$data = indexfungorum_fetch($identifier->id);
				break;
			
			case 'ION_NAME':
				$data = ion_fetch($identifier->id);
				break;
			
			case 'IPNI_NAME':
				$data = ipni_fetch($identifier->id);
				break;
			
			case 'ORCID':
				// Resolve ORCID set flag for whether we look up references that don't have DOIs
				$data = orcid_fetch($identifier->id, false);
				break;
		/*
			case 'PMID':
				$data = pubmed_fetch($identifier->id);
				break;
		*/	
		
			case 'WSC_NAME':
				$data = wsc_fetch($identifier->id);
				break;		
		
			case 'ZOOBANK_PUB':
				$data = zoobank_fetch_pub($identifier->id);
				break;
		
			default:
				break;
		}
	}
	return $data;
}

// test
if (0)
{
	$url = 'http://dx.doi.org/10.1080/00222934908526725';
	
	//$url = 'http://www.worldcat.org/issn/0075-5036';
	
	//$url = 'http://www.worldcat.org/issn/1313-2970';
	
	//$url = 'http://www.ncbi.nlm.nih.gov/pubmed/27058864';
	
	//$url = 'http://bins.boldsystems.org/index.php/Public_RecordView?processid=ASANQ054-09';
	
	//$url = 'http://www.ncbi.nlm.nih.gov/nuccore/146428523';
	
	$url = 'http://ci.nii.ac.jp/naid/110003758633#article';

	$url = 'urn:lsid:zoobank.org:pub:F1DE2C0F-1C90-468E-856B-4A0BCEC56A07';
	
	$data = resolve_url($url);
	print_r($data);

}

?>