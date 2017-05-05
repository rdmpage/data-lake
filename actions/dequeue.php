<?php

// Dequeue some objects

require_once(dirname(dirname(__FILE__)) . '/queue/queue.php');

while (!queue_is_empty())
{
	dequeue(100);
}


?>
