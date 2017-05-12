<?php

require_once(dirname(dirname(__FILE__)) . '/lib.php');
require_once(dirname(dirname(__FILE__)) . '/shared/ncbi.php');

require_once (dirname(dirname(dirname(__FILE__))) . '/documentstore/couchsimple.php');


//----------------------------------------------------------------------------------------
// CrossRef API
function get_work($doi)
{
	global $couch;
	$force = true;

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
			// see http://data.crossref.org/schemas/common4.3.7.xsd
			if (isset($data->message->reference))
			{
				// extract and add cited literature to database
				
				foreach ($data->message->reference as $cited) {
				
					$doc = new stdclass;
					$doc->message = $reference;

					$doc->{'message-format'} = 'application/vnd.crossref-citation+json'; // made up by rdmp	
					$doc->{'message-timestamp'} = date("c", time());
					$doc->{'message-modified'} 	= $doc->{'message-timestamp'};
					
					$doc->_id = 'http://identifiers.org/doi/' . $doi . '#' . $cited->key;
					
					$cited->{'cited-by'} = 'http://identifiers.org/doi/' . $doi;
					
					// utilities for searching
					$cited->query = new stdclass;
					
					// OpenURL
					$fragments = array();
					foreach ($cited as $k => $v)
					{
						switch ($k)
						{
							case 'author':
								$fragments['au'] = $v;
								break;
								
							case 'journal-title':
								$fragments['title'] = $v;
								break;

							case 'article-title':
								$fragments['atitle'] = $v;
								break;

							case 'first-page':
								$fragments['spage'] = $v;
								break;
								
							case 'volume':
							case 'year':
								$fragments[$k] = $v;
								break;
								
							default:
								break;
						}
							
					}
					
					if (count($fragments) > 2)
					{
						$cited->query->openurl = http_build_query($fragments);				
					}
					
					// text string
					if (isset($cited->unstructured))
					{
						$cited->query->string = $cited->unstructured;
					}
					else
					{
						$cited->query->string = '';
						$keys = array('au', 'year', 'atitle', 'title', 'volume', 'spage');
						foreach ($keys as $k)
						{
							$cited->query->string .= ' ' . $fragments[$k];
						}
						$cited->query->string = trim($cited->query->string);
					}
						
					// to do: do we want to try and resolve any of these on the fly?
					
					$doc->message = $cited;
				
					// add to database----------------------------------------------------
					$exists = $couch->exists($doc->_id);
					if (!$exists)
					{
						$couch->add_update_or_delete_document($doc, $doc->_id, 'add');	
					}
					else
					{
						if ($force)
						{
							$couch->add_update_or_delete_document($doc, $doc->_id, 'update');
						}
					}
				
				}
				
			}
			
			
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

if (1)
{
	$doi = '10.1371/journal.pone.0139421'; // no links to XML
	
	$doi = '10.3897/zookeys.520.6185'; // has links to XML
	
	$doi = '10.7554/eLife.08347';
	
	$doi = '10.1038/sdata.2015.35';
	
	$doi = '10.15585/mmwr.mm6503e3';
	
	// Three new species of Begonia (Begoniaceae) from Bahia, Brazil
	$doi = '10.3897/phytokeys.44.7993'; 
	
	//$doi = '10.1016/j.ympev.2011.05.006';
	
	$doi = '10.3897/zookeys.446.8195';
	
	// CrossRef metadata has ORCIDs
	//$doi = '10.7554/eLife.08347'; 
	
	$doi = '10.1007/s12225-010-9229-9'; 
	
	$data = crossref_fetch($doi);
	
	print_r($data);
}

?>
