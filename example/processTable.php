<?php 
	
	require_once("../src/HTML2JSON.php");

	$settings = 
	[
		"url" => "table.html"
	];

	$example = new DOMTable2JSON($settings);
	$example->dumpJSON("data.JSON");

	$json = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/RMT/data/cutdownRoadmap.html");

 ?>