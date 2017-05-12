<?php

// ORCID API

require_once (dirname(dirname(__FILE__)) . '/lib.php');
require_once (dirname(dirname(__FILE__)) . '/nameparse.php');
require_once (dirname(dirname(__FILE__)) . '/fingerprint.php');
require_once (dirname(dirname(__FILE__)) . '/shared/crossref.php');

require_once (dirname(dirname(dirname(__FILE__))) . '/documentstore/couchsimple.php');


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
	global $couch;
	
	$force = true;

	$links = array();
	
	// ORCID
	$orcid = $obj->{'orcid-profile'}->{'orcid-identifier'}->uri;	
	
	// Extract works and create CSL JSON objects for each one
	if (isset($obj->{'orcid-profile'}->{'orcid-activities'}))
	{
		$works = $obj->{'orcid-profile'}->{'orcid-activities'}->{'orcid-works'}->{'orcid-work'};
		
		if ($works)
		{
			foreach ($works as $work)
			{
				$doc = new stdclass;
				
				// Use put-code as bnode identifier for this work
				$doc->_id = $orcid . '-' . $work->{'put-code'};
				$doc->{'message-format'} = 'application/vnd.crossref-api-message+json';		
			
				// Get reference details--------------------------------------------------
				$reference = new stdclass;
				$reference->title = $work->{'work-title'}->{'title'}->value;
	
				// Journal?
				if (isset($work->{'journal-title'}->value))
				{
					$reference->{'container-title'}[] = $work->{'journal-title'}->value;
					$reference->type = 'journal-article';
				}		
		
				if (isset($work->{'publication-date'}))
				{
					$reference->issued = new stdclass;
					$reference->issued->{'date-parts'} = array();
					if (isset($work->{'publication-date'}->{'year'}->value))
					{
						$reference->issued->{'date-parts'}[] = array($work->{'publication-date'}->{'year'}->value);
					}
				}		

				// Parse BibTex-----------------------------------------------------------
				if (isset($work->{'work-citation'}->citation))
				{
					$bibtext = $work->{'work-citation'}->citation;
		
					if (!isset($work->{'journal-title'}->value))
					{
						if (preg_match('/journal = \{(?<journal>.*)\}/Uu', $bibtext, $m))
						{
							$reference->{'container-title'}[] = $m['journal'];
							$reference->type = 'journal-article';
						}
					}
	
					if (!isset($reference->issued))
					{
						$reference->issued = new stdclass;
						$reference->issued->{'date-parts'} = array();
						if (preg_match('/year = \{(?<year>[0-9]{4})\}/', $bibtext, $m))
						{
							$reference->issued->{'date-parts'}[] = array($m['year']);
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
						$reference->page = $m['pages'];
						$reference->page = str_replace('--', '-', $reference->page);
					}
				}
				
				//------------------------------------------------------------------------
				if (isset($work->{'work-contributors'}))
				{		
					$reference->author = array();
				
					// OK, since this person is an author, find the best matching name amongst
					// and use the ORCID for that person. The others will be blank nodes.
					// ORCID has a field "contributor-orcid" but this always seems to be null :(

					foreach ($work->{'work-contributors'}->{'contributor'} as $contributor)
					{
						$author = new stdclass;
											
						// Parse the name
						$parts = parse_name($contributor->{'credit-name'}->value);
		
						if (isset($parts['last']))
						{
							$author->family = $parts['last'];
						}
						if (isset($parts['first']))
						{
							$author->given = $parts['first'];
			
							if (array_key_exists('middle', $parts))
							{
								$author->given .= ' ' . $parts['middle'];
							}
						}
						
						if (!isset($author->family) || !isset($author->given))
						{
							$author->literal = $contributor->{'credit-name'}->value;
						}
					
						$reference->author[] = $author;
					}
				}
				
				// match to ORCID
				// code from recon-15
				if (isset($reference->author))
				{
				
					$target = new stdclass;
					$target->name = '';

					$personal_details = $obj->{'orcid-profile'}->{'orcid-bio'}->{'personal-details'};

					if (isset($personal_details->{'given-names'}))
					{
						$target->name = $personal_details->{'given-names'}->{'value'};
					}

					if (isset($personal_details->{'family-name'}))
					{
						$target->name .= $personal_details->{'family-name'}->{'value'};
					}

					if (isset($personal_details->{'credit-name'}))
					{
						$target->credit_name = $personal_details->{'credit-name'}->{'value'};
					}

					if (isset($personal_details->{'other-names'}))
					{
						foreach ($personal_details->{'other-names'}->{'other-name'} as $other_name)
						{
							$target->other_names[] = $other_name->value;
						}
					}				
					$min_d = 100;
					$hit = -1;

					$n = count($reference->author);
					for ($i = 0; $i < $n; $i++)
					{
						$name = '';
						if (isset($reference->author[$i]->literal))
						{
							$name = $reference->author[$i]->literal;
						}
						else
						{
						 $name = $reference->author[$i]->given . ' ' . $reference->author[$i]->family;
						}
					
					
						if (isset($target->name))
						{
							$d = levenshtein(finger_print($name), finger_print($target->name));

							if ($d < $min_d)
							{
								$min_d = $d;
								$hit = $i;
							}
						}

						if (isset($target->credit_name))
						{
							$d = levenshtein(finger_print($name), finger_print($target->credit_name));

							if ($d < $min_d)
							{
								$min_d = $d;
								$hit = $i;
							}
						}

					}
					
					if ($hit != -1)
					{
						$reference->author[$hit]->ORCID = $orcid;
					}						
				}
		
				// Identifiers------------------------------------------------------------
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
								$reference->DOI = $value;
								break;
						
							case 'ISBN':
								$value = $identifier->{'work-external-identifier-id'}->value;
						
								if ($work_type == 'BOOK')
								{
									$reference->isbn[] = $value;
								}												
								break;

							case 'ISSN':
								$value = $identifier->{'work-external-identifier-id'}->value;
								$parts = explode(";", $value);
						
								$reference->ISSN[] = $parts;
								break;

							case 'PMC':
								$value = $identifier->{'work-external-identifier-id'}->value;
								$reference->PMC = $value;
								break;

							case 'PMID':
								$value = $identifier->{'work-external-identifier-id'}->value;
								$reference->PMID = $value;
								break;

							case 'WOSUID':
								$value = $identifier->{'work-external-identifier-id'}->value;
								$reference->WOSUID = $value;
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
						$reference->URL = $urls[0];
					}
				}
				
				// Support for queries ---------------------------------------------------
				// Construct text string and OpenURL to help queries
				// Could do this in Javascript in CouchDB instead,
				// but we will also use it hear if we do on the fly lookup
				$reference->query = new stdclass;
								
				$fragments = array();
				
				if (isset($reference->author))
				{
					$authors = array();
					foreach ($reference->author as $author)
					{
						$author_string = '';
						if (isset($author->literal))
						{
							$author_string = $author->literal;
						}
						else
						{
							if (isset($author->given))
							{
								$author_string = $author->given;
							}
							if (isset($author->family))
							{
								$author_string .= ' ' . $author->family;
							}
							$author_string = trim($author_string);
						}
						if ($author_string != '')
						{
							$authors[] = $author_string;
						}					
					}
					$fragments['au'] = join('au=', $authors);
				}
								
				if (isset($reference->issued))
				{
					if (isset($reference->issued->{'date-parts'}))
					{
						$fragments['year'] = $reference->issued->{'date-parts'}[0][0];
					}
				}
				
				if (isset($reference->title))
				{
					$fragments['atitle'] = $reference->title;
				}

				if (isset($reference->{'container-title'}))
				{
					$fragments['title'] = $reference->{'container-title'}[0];
				}

				if (isset($reference->volume))
				{
					$fragments['volume'] = $reference->volume;
				}

				if (isset($reference->issue))
				{
					$fragments['issue'] = $reference->issue;
				}

				if (isset($reference->page))
				{
					$pages = $m['pages'];
					if (preg_match('/(?<spage>\d+)-[-]?(?<epage>\d+)/', $reference->page, $mm))
					{
						$fragments['spage'] = $mm['spage'];
						$fragments['epage'] = $mm['epage'];
					}
					else
					{	
						$fragments['pages'] = $reference->page;
					}
				}
					
				
				
				//print_r($fragments);
						
				// simple OpenURL query string
				$reference->query->openurl = http_build_query($fragments);				
				$reference->query->openurl = str_replace('au%3D', '&au=', $reference->query->openurl);
				
				// Simple text string for text-matching searches such as https://search.crossref.org
				$reference->query->string = '';
				$keys = array('au', 'year', 'atitle', 'title', 'volume', 'issue', 'spage', 'epage', 'pages');
				foreach ($keys as $k)
				{
					switch ($k)
					{
						case 'au':
							$reference->query->string = str_replace('au=', '; ', $fragments[$k]);
							break;
							
						default:
							$reference->query->string .= ' ' . $fragments[$k];
							break;
					}
				}
							
				// Create structure to track what searches have been made for identifiers
				// to do: need to fix this code
				//if ($lookup_works)
				if (1)
				{
					// look for DOI if not present
					if (!isset($reference->DOI) && ($reference->query->string != ''))
					{
						echo "Looking up \"" . $reference->query->string . "\"... ";
						$result = crossref_search($reference->query->string, true, 0.75);
						if (isset($result->doi))
						{
							echo $result->doi;
							$reference->DOI = $result->doi;
						}
						echo "\n";
					}
				}
				

				$doc->message = $reference;
				
				$doc->{'message-timestamp'} = date("c", time());
				$doc->{'message-modified'} 	= $doc->{'message-timestamp'};
				
				//print_r($doc);
				//exit();
				
				//echo json_encode($doc) . "\n";
				
				// add to database--------------------------------------------------------
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
				
				// links for this work
				$link = '';
				if ($link == '')
				{
					if (isset($reference->DOI))
					{
						$link = 'http://dx.doi.org/' . $reference->DOI;
					}
				}
				if ($link == '')
				{
					if (isset($reference->PMID))
					{
						$link = 'http://www.ncbi.nlm.nih.gov/pubmed/' . $reference->PMID;
					}		
				}
				if ($link == '')
				{
					if (isset($reference->PMC))
					{
						$link = 'http://www.ncbi.nlm.nih.gov/pmc/articles/' . $reference->PMC;
					}		
				}

				if ($link != '')
				{
					$links[] = $link;
				}
		
				// links for container
				if (isset($reference->ISSN))
				{
					foreach ($reference->ISSN as $issn)
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
	
	//$json = file_get_contents(dirname(__FILE__) . '/0000-0003-0566-372X.json');
	//file_put_contents(dirname(__FILE__) . '/0000-0003-0566-372X.json', $json);
	
	
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
