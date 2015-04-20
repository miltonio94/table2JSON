<?php 
	// require_once $_SERVER['DOCUMENT_ROOT'] . "/RMT/Lib/table2JSON/HTMLTable2JSON.php";

	// $tableJSON = new HTMLTable2JSON();

	// $cols = [2, 21, 35, 36, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55];

	// $dataJSON = $tableJSON->tableToJSON("http://localhost/RMT/data/cutdownRoadmap.html", true, '', NULL, NULL, false, $cols, false, false, true, null);

	// print_r($dataJSON);
	ini_set('display_errors', 'On');
	ini_set('memory_limit', '-1');

	class DOMTable2JSON
	{

		private $html;
		private $tableNode;
		private $thNodes;
		private $destJSON;
		private $trNodes; // tr from body only
		private $colLabel;
		private $settings;
		private $ttable; //array that will contain the table structure

		/**
		**	@url : string with a url of the file containing the table
		**	@setting 	: array containing settings
		** 					settings options:
		**					colOnly => [an array containing the indexed colums that you wish to process],
		**					colIgnore => [array of indexes of the collums to be ignored],
		**					destination => "string for the destination of where you want the file to go",
		**					
		**					
		**/
		function __construct($url, $setting = null)
		{
			$this->colLabel = array();
			$this->settings = $setting;

			//loading html file as a string
			$c = curl_init($url);
			curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
			$this->html = curl_exec($c);

			//assigning html a DOM object
			$this->tableNode = new DOMDocument();
			$this->tableNode->loadHTML($this->html);//loading the html document into the DOM object variable
			
			//getting all the headings for the table in a DOM format
			$this->thNodes = $this->tableNode->getElementsByTagName("th");

			//getting all the table rows in tbody
			$tbodyNode = $this->tableNode->getElementsByTagName("tbody");
			$this->trNodes = $tbodyNode->item(0)->childNodes;

			//will set colums labels
			$this->setHeaders();
			$this->structuringdData();
		}		

		private function setHeaders()
		{
			if(!isset($this->settings["colOnly"]) && isset($this->settings["colIgnore"]))
			{
				echo "Error: You can't set both colOnly and colIgnore";
				return;
			}

			else
			{
				//if setting for filtering collums was not set add all collumns to
				if(!isset($this->settings["colOnly"]) && !isset($this->settings["colIgnore"]))
				{
					foreach ($this->thNodes as $th) 
					{
						array_push($this->colLabel, $th->textContent);
					}
				}

				//will only set the collums that have been specified
				if(isset($this->settings["colOnly"]))
				{
					foreach ($this->settings["colOnly"] as $key) 
					{
						array_push($this->colLabel ,$this->thNodes->item($key)->textContent);
					}
				}
			
				// TODO: add exception for processing all collums except for the ignored indexed parsed through the array setting
			}
		}

		// this function will structure the table into an array in the format of [ rowx => [colname => col0, colname => col1, colname => col2]...]
		private function structuringdData()
		{
			for($i = 0; $i < $this->trNodes.length)
			{
				array_push($this->table, "row" + i => array());
			}

			print_r($this->table);
		}
	}

	$settings = 
	[
		"colOnly"=>[2,21,36,37,42,43,44,45,46,47,48,49,50,51,52,53,54,55]
	];

	$exmpl = new DOMTable2JSON("http://localhost/RMT/data/cutdownRoadmap.html", $settings);

?>