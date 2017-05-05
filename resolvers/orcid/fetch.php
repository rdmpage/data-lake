<?php

// ORCID API

require_once (dirname(dirname(__FILE__)) . '/lib.php');
require_once (dirname(dirname(__FILE__)) . '/shared/crossref.php');


/*
  Problems with ORCID:
  
  1. Only the person whose profile this is has is identified by an ORCID. Coauthors
     with ORCIDs don't have them (sigh).
     
  2. Many works lack DOIs in the ORCID profile, even if they actually have them. Need to
     think about whether we go hunting for these.
*/

//----------------------------------------------------------------------------------------
function orcid_works($obj, $lookup_works)
{
	$links = array();
	
	$works = array();
	
	// Extract works
	
	if (isset($obj->{'orcid-profile'}->{'orcid-activities'}))
	{
		$works = $obj->{'orcid-profile'}->{'orcid-activities'}->{'orcid-works'}->{'orcid-work'};
		
		if ($works)
		{
			foreach ($works as $work)
			{
				$reference = new stdclass;
	
				// Use put-code as bnode identifier
				$reference->id = $work->{'put-code'};
		
				$reference->title = $work->{'work-title'}->{'title'}->value;
	
				// Journal?
				if (isset($work->{'journal-title'}->value))
				{
					$reference->journal = $work->{'journal-title'}->value;
				}		
		
				// date
				$date = '';
				if (isset($work->{'publication-date'}))
				{
					if (isset($work->{'publication-date'}->{'year'}->value))
					{
						$date = $work->{'publication-date'}->{'year'}->value;
					}
					$reference->date = $date;
				}
		

				// Parse BibTex-------------------------------------------------------------------
				if (isset($work->{'work-citation'}->citation))
				{
					$bibtext = $work->{'work-citation'}->citation;
		
					if (!isset($work->{'journal-title'}->value))
					{
						if (preg_match('/journal = \{(?<journal>.*)\}/Uu', $bibtext, $m))
						{
							$reference->journal = $m['journal'];
						}
					}
	
					if ($date == '')
					{
						if (preg_match('/year = \{(?<year>[0-9]{4})\}/', $bibtext, $m))
						{
							$reference->date = $m['year'];
						}
					}
			
					if (preg_match('/volume = \{(?<volume>.*)\}/Uu', $bibtext, $m))
					{
						$reference->volume = $m['volume'];
					}

					if (preg_match('/number = \{(?<issue>.*)\}/Uu', $bibtext, $m))
					{
						$reference->issue = $m['issue'];
					}

					// pages = {41-68}
					if (preg_match('/pages = \{(?<pages>.*)\}/Uu', $bibtext, $m))
					{
						$pages = $m['pages'];
						if (preg_match('/(?<spage>\d+)-[-]?(?<epage>\d+)/', $pages, $mm))
						{
							$reference->pageStart = $mm['spage'];
							$reference->pageEnd = $mm['epage'];
						}
						else
						{	
							$reference->pages = $pages;
						}
					}
				}
		
				// Identifiers
				if (isset($work->{'work-external-identifiers'}))
				{
					foreach ($work->{'work-external-identifiers'}->{'work-external-identifier'} as $identifier)
					{
						switch ($identifier->{'work-external-identifier-type'})
						{
							case 'DOI':
								$value = $identifier->{'work-external-identifier-id'}->value;
								// clean
								$value = preg_replace('/^doi:/', '', $value);
								$value = preg_replace('/\.$/', '', $value);
								$value = preg_replace('/\s+/', '', $value);
					
								// DOI
								$reference->doi = $value;
								break;
						
							case 'ISBN':
								$value = $identifier->{'work-external-identifier-id'}->value;
						
								if ($work_type == 'BOOK')
								{
									$reference->isbn = $value;
								}												
								break;

							case 'ISSN':
								$value = $identifier->{'work-external-identifier-id'}->value;
								$parts = explode(";", $value);
						
								$reference->issn = $parts;
								break;

							case 'PMC':
								$value = $identifier->{'work-external-identifier-id'}->value;
								$reference->pmc = $value;
								break;

							case 'PMID':
								$value = $identifier->{'work-external-identifier-id'}->value;
								$reference->pmid = $value;
								break;
					
							default:
								break;
						}
					}
				}
	
				// URL
				if (isset($work->{'url'}))
				{
					if (isset($work->{'url'}->{'value'}))
					{
						$urls = explode(",", $work->{'url'}->{'value'});
						$reference->url = $urls[0];
					}
				}
				
				if ($lookup_works)
				{
					// look for DOI if not present
					if (!isset($reference->doi))
					{
						$citation = '';
						if (isset($reference->title))
						{
							$citation = $reference->title;
						}
						if (isset($reference->journal))
						{
							$citation .= ' ' . $reference->journal;
						}

						if ($citation != '')
						{		
							echo "Looking up \"$citation\"... ";
							$result = crossref_search($citation);
							if (isset($result->doi))
							{
								echo $result->doi;
								$reference->doi = $result->doi;
							}
							echo "\n";
						}
					}
				}

				// keep track of these for now
				$works[] = $reference;
		
				// links for this work
				$link = '';
				if ($link == '')
				{
					if (isset($reference->doi))
					{
						$link = 'http://dx.doi.org/' . $reference->doi;
					}
				}
				if ($link == '')
				{
					if (isset($reference->pmid))
					{
						$link = 'http://www.ncbi.nlm.nih.gov/pubmed/' . $reference->pmid;
					}		
				}
				if ($link == '')
				{
					if (isset($reference->pmc))
					{
						$link = 'http://www.ncbi.nlm.nih.gov/pmc/articles/' . $reference->pmc;
					}		
				}

				if ($link != '')
				{
					$links[] = $link;
				}
		
				// links for container
				if (isset($reference->issn))
				{
					foreach ($reference->issn as $issn)
					{				
						$link = 'http://www.worldcat.org/issn/' . $issn;
						if (!in_array($link, $links))
						{
							$links[] = $link;
						}
					}
				}		
	
			}
		}
	}	
	
	$links = array_unique($links);
	
	return $links;	
	
}


//----------------------------------------------------------------------------------------
function orcid_fetch($orcid, $lookup_works = false)
{
	$data = null;
	
	
	$url = 'http://pub.orcid.org/v1.2/' . $orcid . '/orcid-profile';
	$json = get($url, '', 'application/orcid+json');
	
//	$json = file_get_contents('0000-0002-7573-096X.json');
	
	//echo $json;
	
	//$json = file_get_contents($orcid . '.json');

	//$json = file_get_contents('0000-0003-3941-5628.json');
	
	if ($json != '')
	{
		$data = new stdclass;
		
		$data->{'message-format'} = 'application/vnd.orcid+json';		
		$data->message = json_decode($json);
		
		$data->links = orcid_works($data->message, $lookup_works);
		
		// for now (debugging)
		//unset($data->content);

		//print_r($data);
	}
	
	return $data;
}


if (0)
{
	//orcid_fetch('0000-0002-7573-096X');

	//orcid_fetch('0000-0002-7941-346X');

	//orcid_fetch('0000-0001-8916-5570');

	orcid_fetch('0000-0003-0566-372X');
}


?>
