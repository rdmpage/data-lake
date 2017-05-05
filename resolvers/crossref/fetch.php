<?php

require_once(dirname(dirname(__FILE__)) . '/lib.php');
require_once(dirname(dirname(__FILE__)) . '/shared/ncbi.php');


//----------------------------------------------------------------------------------------
// CrossRef API
function get_work($doi)
{
	$data = null;
	
	$url = 'https://api.crossref.org/v1/works/http://dx.doi.org/' . $doi;
	
	$json = get($url);
	
	if ($json != '')
	{
		$obj = json_decode($json);
		if ($obj)
		{
			$data = new stdclass;
			
			$data->{'message-format'} = 'application/vnd.crossref-api-message+json';
						
			$data->message = $obj->message;
			
			// authors
			if (isset($data->message->author))
			{
				foreach ($data->message->author as $author)
				{
					if (isset($author->ORCID))
					{
						$data->links[] = $author->ORCID;
					}
				}
			}
			
			// cited literature (ensure we use same logic when naming these as in CouchDB view)
			
			// funders
			
			
			// augment
			/*
			$pmid = doi_to_pmid($doi);
			if ($pmid != 0)
			{
				$data->message->pmid = $pmid;
				
				// add to link to PMID so we augment this reference
				// need to think this through regarding authors and other info which may be replicated
				$data->links[] = 'http://www.ncbi.nlm.nih.gov/pubmed/' . $data->message->pmid;
			}
			*/
			
			/*
			$pmc = doi_to_pmc($doi);
			if ($pmc != 0)
			{
				$data->content->pmc = 'PMC' . $pmc;
				
				// add to link to PMID so we augment this reference
				// need to think this through regarding authors and other info which may be replicated
				$data->alternative_identifiers[] = 'http://www.ncbi.nlm.nih.gov/pmc/articles/' . $data->content->pmc;
				
				$data->content->cites = pmc_cites_in_pubmed($pmc);	
				$data->content->cited_by = pmc_cited_by_pmc($pmc);
			}
			*/
			
		}
	}
	
	return $data;
}


//----------------------------------------------------------------------------------------
function crossref_fetch($doi)
{
	$data = get_work($doi);
	return $data;
}


// test cases

if (0)
{
	$doi = '10.1371/journal.pone.0139421'; // no links to XML
	
	$doi = '10.3897/zookeys.520.6185'; // has links to XML
	
	$doi = '10.7554/eLife.08347';
	
	$doi = '10.1038/sdata.2015.35';
	
	$doi = '10.15585/mmwr.mm6503e3';
	
	// Three new species of Begonia (Begoniaceae) from Bahia, Brazil
	$doi = '10.3897/phytokeys.44.7993'; 
	
	//$doi = '10.1016/j.ympev.2011.05.006';
	
	//$doi = '10.3897/zookeys.446.8195';
	
	// CrossRef metadata has ORCIDs
	$doi = '10.7554/eLife.08347';
	
	$data = crossref_fetch($doi);
	
	print_r($data);
}

?>
