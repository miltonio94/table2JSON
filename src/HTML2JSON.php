<?php 

	/**
	*
	* 	This class will firstly structure the table in the following way:
	*
	* 	table => [
	* 				row1 => [data, data, data],
	* 				row2 => [data, data, data],
	* 				row1 => [data, data, data]			
	* 			]
	*    
	*    the amount of rows and data(collum) will depend onyour table size
	*
	* 	after processing the data it will turnit into JSON and output it to a file
	* 	by using the dumpJSON() methodwhich takes a $url of where you want the data to go to
	*
	*/

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
		private $table; //array that will contain the table structure

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
			//assinig values to the variables that will be used for the process
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

			//check if usser accidently used both possible filtering and displaying adequate error
			if(isset($this->settings["colOnly"]) && isset($this->settings["colIgnore"]))
			{
				echo "Error: You can't set both colOnly and colIgnore";
				return;
			}
			else
			{
				$this->setHeaders();		//will set colums labels
				$this->structTableArr();	//will structure the array representation of the table
				$this->addingData();		//will add data to the array representation of the table
			}
		}		

		private function isColOnly()
		{
			if(isset($this->settings["colOnly"]))
			{
				return true;
			}
			else
			{
				return false;
			}

		}

		private function isIgnorCol()
		{
			if(isset($this->settings["colIgnore"]))
			{
				return true;
			}
			else
			{
				return false;
			}

		}

		private function setHeaders()
		{
			//if setting for filtering collums was not set add all collumns to
			if(!$this->isColOnly() && !$this->isIgnorCol())
			{
				foreach ($this->thNodes as $th) 
				{
					array_push($this->colLabel, $th->textContent);
				}
			}

			//will only set the collums that have been specified
			if($this->isColOnly())
			{
				foreach ($this->settings["colOnly"] as $key) 
				{
					array_push($this->colLabel ,$this->thNodes->item($key)->textContent);
				}
			}
		
			// TODO: add exception for processing all collums except for the ignored indexed parsed through the array setting
		}

		// this function will structure the table into an array in the format of [ rowx => [colname => col0, colname => col1, colname => col2]...]
		private function structTableArr()
		{
			$length = $this->trNodes->length;
			//making sure that the table is of the same length as trNodes variables
			for($i = 0; $i < $length; $i++)
			{
				$this->table["row$i"] = array();
			}
		}

		private function addingData()
		{
			$labelLen = count($this->colLabel); //getting the length of the labels array
			$tableLen = count($this->table); //getting the length of the table array should be the same length as $trNode

			//the next 2 loops do pretty much the same thing only one filters the content the other doesnt 
			//so i'm only commenting one of them
			if($this->isColOnly())
			{
				for($i = 0; $i < $tableLen; $i++) //indexes for the node list $trNodes
				{
					$row = $this->trNodes->item($i); //getting row $i from node list object and assigning it to $row
					$tdNodes = $row->getElementsByTagName("td");  //getting a node list with all the td elements in current row

					for($x = 0; $x < $labelLen; $x++) //loop to cycle through the indexes
					{
						// assign to the current row of the table array the value located $x index of array containing the collums to filter from
						$this->table["row$i"][$this->colLabel[$x]] = $tdNodes->item($this->settings["colOnly"][$x])->textContent;
					}
				}
			}

			//same as befor minus the data filtering also esier to read due to not having to implement data filtering
			elseif (!$this->isColOnly() && !$this->isIgnorCol()) 
			{
				for($i = 0; $i < $tableLen; $i++)
				{
					$row = $this->trNodes->item($i);
					$tdNodes = $row->getElementsByTagName("td"); 

					for($x = 0; $x < $labelLen; $x++)
					{
						$this->table["row$i"][$this->colLabel[$x]] = $tdNodes->item($x)->textContent;
					}
				}
			}
		}

		//will dump the JSON at your desired local url
		public function dumpJSON($url)
		{
			$JSON = json_encode($this->table);
			$file = fopen($url, 'w');
			fwrite($file, $JSON);
			fclose($file);
		}
	}

?>