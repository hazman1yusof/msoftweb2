<?php
	$responce = new stdClass();
	$responce->ipaddress =  $_SERVER['REMOTE_ADDR'];
	$responce->computerid = gethostbyaddr($_SERVER['REMOTE_ADDR']);

	echo json_encode($responce);
?>