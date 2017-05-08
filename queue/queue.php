<?php

// Manage a queue of objects

// Queue is managed by views in CouchDB


require_once(dirname(dirname(__FILE__)) . '/documentstore/couchsimple.php');
require_once(dirname(dirname(__FILE__)) . '/resolvers/resolve.php');


//----------------------------------------------------------------------------------------
// Put an item in the queue , optionally force if already exists by deleting item
// and putting it back in the queue.
function enqueue($url, $force = false)
{
	global $config;
	global $couch;
	
	$go = true;
	
	// Check whether this URL already exists (have we done this object already?)
	// to do: what about having multiple URLs for same thing, check this
	$exists = $couch->exists($url);
	
	if ($exists)
	{
		echo "$url Exists\n";
		$go = false;
		
		if ($force)
		{
			echo "[forcing]\n";
			$couch->add_update_or_delete_document(null, $url, 'delete');
			$go = true;		
		}
	}

	if ($go)
	{
		$doc = new stdclass;
		
		// URL is document id and also source (i.e., we will resolve this URL to get details on object)
		$doc->_id = $url;	
		
		// By default message is empty and has timestamp set to "now"
		// This means it will be at the end of the queue of things to add
		$doc->{'message-timestamp'} = date("c", time());
		$doc->{'message-modified'} 	= $doc->{'message-timestamp'};
		$doc->{'message-format'} 	= 'unknown';
		
		$resp = $couch->send("PUT", "/" . $config['couchdb_options']['database'] . "/" . urlencode($doc->_id), json_encode($doc));
		var_dump($resp);
	}

}

//----------------------------------------------------------------------------------------
// True if queue is empty
function queue_is_empty()
{
	global $config;
	global $couch;
	
	$empty = false;
	
	$url = '_design/queue/_view/todo?limit=1';
		
	$resp = $couch->send("GET", "/" . $config['couchdb_options']['database'] . "/" . $url);
	$response_obj = json_decode($resp);

	if (!isset($response_obj->error))
	{
		$empty = ($response_obj->total_rows == 0);
	}
	
	return $empty;

}

//----------------------------------------------------------------------------------------
// Item is a single row from a CouchDB query
function fetch($item, $add_links = false)
{
	global $config;
	global $couch;
	
	// log
	echo "Resolving " . $item->value . "\n";
	//exit();
	
	$data = null;
	$data = resolve_url($item->value);
	
	print_r($data);
	
	if (!$data)
	{
		echo " *** Failed to resolve " . $item->value . "\n";
	
		// No data means we failed to resolve this,
		// keep track of attempts to resolve so we can ignore them
		
		// update document store item with message content
		$resp = $couch->send("GET", "/" . $config['couchdb_options']['database'] . "/" . urlencode($item->value));
		var_dump($resp);
		if ($resp)
		{
			$doc = json_decode($resp);
			if (!isset($doc->error))
			{
				if (isset($doc->{'message-attempts'}))
				{
					$doc->{'message-attempts'}++;
				}
				else
				{
					$doc->{'message-attempts'} = 1;
				}
				
				$resp = $couch->send("PUT", "/" . $config['couchdb_options']['database'] . "/" . urlencode($doc->_id), json_encode($doc));
				var_dump($resp);
			}
		}	
	}
	else
	{
		// if we have message content, update object with that message, which will remove it from the queue
		// Assuming we have set {'message-format'} to one of the MIME types recognised by the CouchDB
		// views, the object will also be indexed by the corresponding view
		if (isset($data->message))
		{
			// Think about how many, if any, links from this item we put in the queue
			if (isset($data->links))
			{				
				// Add links for DOIs (e.g., ORCIDs and ISSNs)
				if (preg_match('/dx.doi.org/', $item->value))
				{
					$add_links = true;
				}
				
				// Resolve links from ORCIDs (this can quickly explode)
				if (preg_match('/orcid.org/', $item->value))
				{
					//$add_links = true;
				}
				
				// Add links for ZooBank
				if (preg_match('/zoobank/', $item->value))
				{
					$add_links = true;
				}
				
				
				if ($add_links)
				{
					foreach ($data->links as $link)
					{
						// log
						echo "Adding " . $link . " to queue\n";
						enqueue($link);
					}
				}
			}
			
			// update document store item with message content
			$resp = $couch->send("GET", "/" . $config['couchdb_options']['database'] . "/" . urlencode($item->value));
			var_dump($resp);
			if ($resp)
			{
				$doc = json_decode($resp);
				if (!isset($doc->error))
				{
					$doc->{'message-modified'} = date("c", time());					
					$doc->{'message-format'} = $data->{'message-format'};
					$doc->message = $data->message;
					
					$resp = $couch->send("PUT", "/" . $config['couchdb_options']['database'] . "/" . urlencode($doc->_id), json_encode($doc));
					var_dump($resp);
				}
			}	
		}		
	}
}

//----------------------------------------------------------------------------------------
// Dequeue one or more objects and fetch them
// 
// to do: if we get just one object, and that fails, we may end up with a queue that is 
// forever stuck, so maybe get a bunch of items, and resolve those.
function dequeue($n = 5, $descending = false)
{
	global $config;
	global $couch;
	
	$url = '_design/queue/_view/todo?limit=' . $n;
	
	if ($descending)
	{
		$url .= "&descending=true";
	}
	
	$resp = $couch->send("GET", "/" . $config['couchdb_options']['database'] . "/" . $url);
	$response_obj = json_decode($resp);

	print_r($response_obj);
		
	// fetch content
	$count = 0;
	foreach ($response_obj->rows as $row)
	{
		fetch($row);	
		
		// Give source a rest
		if (($count++ % 10) == 0)
		{
			$rand = rand(1000000, 3000000);
			echo '...sleeping for ' . round(($rand / 1000000),2) . ' seconds' . "\n";
			usleep($rand);
		}
		
	}
		
}

//----------------------------------------------------------------------------------------
// Load one item directly into database without waiting for it to be dequeued
function load_url($url)
{
	// Ensure item is in the queue 
	enqueue($url);
	
	// simulate the result of a CouchDB query by creating an item that has
	// the URL to resolve as it's value
	$item = new stdclass;
	$item->value = $url;
	// fetch the item
	fetch($item);
}



?>
