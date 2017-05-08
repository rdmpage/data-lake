<?php

// Parse meta tags for article and add to database (possibly including citations)

require_once(dirname(dirname(__FILE__)) . '/lib.php');
require_once (dirname(dirname(dirname(__FILE__))) . '/vendor/simplehtmldom_1_5/simple_html_dom.php');


function fetch_meta($url)
{
	$html = get($url);
	
	//$html = file_get_contents('oup.html');

	if ($html != '')
	{

		$dom = str_get_html($html);

		$metas = $dom->find('meta');
	
	
		foreach ($metas as $meta)
		{
			echo $meta->name . "=" . html_entity_decode($meta->content) . "\n";
		}
		
		
		$citation = new stdclass;
		$citation->author = array();
		$citation->issn = array();
		$citation->keywords = array();
		$citation->reference = array();
		
		foreach ($metas as $meta)
		{
			$meta->content = html_entity_decode($meta->content);
				
			switch ($meta->name)
			{

				// Google	
				case 'citation_author':
					if (!in_array($meta->content, $citation->author))
					{
						$citation->author[] =  $meta->content;
					}
					break;

				case 'citation_title':
					$citation->title = trim($meta->content);
					$citation->title = preg_replace('/\s\s+/u', ' ', $citation->title);
					break;

				case 'citation_doi':
					$citation->doi =  $meta->content;
					break;

				case 'citation_journal_title':
					$citation->journal_title =  $meta->content;
					break;

				case 'citation_issn':
					$citation->issn[] =  $meta->content;
					break;

				case 'citation_keywords':
					$citation->keywords[] =  $meta->content;
					break;

				case 'citation_reference':
				
					$reference = new stdclass;
					$reference->citation = $meta->content;
					$reference->author = array();
					
					$parts = preg_split('/;\s+citation_/u', $meta->content);
					
					if (count($parts) > 1)
					{
						$reference->citation = '';
						
						foreach ($parts as $part)
						{
							list($key, $value) = preg_split('/=/u', $part);
							
							$key = preg_replace('/^citation_/u', '', $key);
							
							$reference->citation .= ' ' . $value;
							
							switch ($key)
							{
								case 'author':
									$reference->author[] = $value;
									break;
									
								default:
									$reference->{$key} = $value;
									break;
							}
						}
								
					}
					else
					{
						// Can we parse the reference?
					
					
					}

					if (count($reference->author) == 0)
					{
						unset($reference->author);
					}
					$reference->citation = trim($reference->citation);

					$citation->reference[] =  $reference;
					break;

				case 'citation_volume':
					$citation->volume =  $meta->content;
					break;

				case 'citation_issue':
					$citation->issue =  $meta->content;
					break;

				case 'citation_firstpage':
					$citation->firstpage =  $meta->content;
			
					if (preg_match('/(?<spage>\d+)[-|-](?<epage>\d+)/u', $meta->content, $m))
					{
						$citation->firstpage =  $m['spage'];
						$citation->lastpage =  $m['epage'];
					}
					break;

				case 'citation_lastpage':
					$citation->lastpage =  $meta->content;
					break;
					
				case 'citation_publisher':
					$citation->publisher =  $meta->content;
					break;
					
				case 'citation_abstract_html_url':
					$citation->abstract_html_url = $meta->content;
					break;
					
				case 'citation_pdf_url':
					$citation->pdf_url =  $meta->content;
					break;
					
				case 'citation_xml_url':					
					$citation->xml_url =  $meta->content;
					break;			

				case 'citation_date':
				case 'citation_publication_date':
					$citation->date = $meta->content;
					if (preg_match('/^[0-9]{4}$/', $meta->content))
					{
						$citation->year = $meta->content;
					}
					if (preg_match('/^(?<year>[0-9]{4})\//', $meta->content, $m))
					{
						$citation->year = $m['year'];
					}
					if (preg_match('/^([0-9]{4})\/(\d+)\/(\d+)/', $meta->content, $m))
					{
						$citation->date = $m[1] . '-' . $m[2] . '-' . $m[3];
					}
					break;

				default:
					break;
			}
		}	
		
		// cleaning
		if (count($citation->author) == 0)
		{
			unset($citation->author);
		}
		if (count($citation->issn) == 0)
		{
			unset($citation->issn);
		}
		if (count($citation->keywords) == 0)
		{
			unset($citation->keywords);
		}
		if (count($citation->reference) == 0)
		{
			unset($citation->reference);
		}
		
		print_r($citation);
		
	}				

}


// test
if (1)
{
	// J-Stage with references
	$url = 'https://www.jstage.jst.go.jp/article/asjaa1936/51/1/51_1_33/_article';
	
	// Japanese
	//$url = 'https://www.jstage.jst.go.jp/article/asjaa1936/5/4/5_4_224/_article';
	
	// OUP with their own reference style
	//$url = 'https://academic.oup.com/jmammal/article/95/5/943/984478/The-valid-generic-name-for-red-backed-voles';
	
	fetch_meta($url);
}

?>