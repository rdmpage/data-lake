<?php

// Parse meta tags for article and add to database (possibly including citations)

require_once(dirname(dirname(__FILE__)) . '/lib.php');
require_once (dirname(dirname(dirname(__FILE__))) . '/vendor/simplehtmldom_1_5/simple_html_dom.php');




function fetch_meta($url)
{
	$html = get($url);

	if ($html != '')
	{

		$dom = str_get_html($html);

		$metas = $dom->find('meta');
	
		foreach ($metas as $meta)
		{
			echo $meta->name . "=" . html_entity_decode($meta->content) . "\n";
		}
	}				

}


// test
if (1)
{
	$url = 'https://www.jstage.jst.go.jp/article/asjaa1936/51/1/51_1_33/_article';
	fetch_meta($url);
}

?>