<?php
		
	/* Class deputated to parse a header list.
	Error coding:		001 missing parameters
						017 header is an array


	*/

	// Includes.
	require_once("synset.class.php");

	
	class Parser {
		
		// Internal service attributes.
		private $header_string, $separator, $delimiter, $header_array, $header_token, $domains, $categs, $synset, $weight, $table_categs, $table_domains;

		// Public attributes.
		public $a;

		// Output attributes.
		public $message, $errlog, $status;

		// Parameters and configuration attributes.
		public $categ_k	= 1;
		public $domain_k	= 0.75;


		//////////////////////////////////////////////////////////////////////////////////////////
		//																						//
		//										CONSTRUCTOR										//
		//																						//
		//////////////////////////////////////////////////////////////////////////////////////////


		// Requires the header, a separator character and a delimiter character.
		public function Parser($header, $separator, $delimiter){
			
			if ($header && $separator && $delimiter){
				if (is_array($header)){
					$this->message	= "Error code 017:header is an array. [Parser.Parser]";
					$this->errlog	.= "[".date("d-m-o H:i:s")."] ".$this->message."\n";
					$this->status	= FALSE;
				}else{
					$this->header_string	= $header;
					$this->separator		= $separator;
					$this->delimiter		= $delimiter;
					$this->header_array		= 
					$this->header_token		= array();
					$this->message			= "Class Parser instanced successfully. [Parser.Parser]";
					$this->errlog			.= "[".date("d-m-o H:i:s")."] ".$this->message."\n";
					$this->status			= TRUE;
					$this->headerToArray();
					$this->tokenizate();
					$this->buildSynsets();
					$this->calculateWeightByTable();
				}
			}else{
				$this->message	= "Error code 001: missing parameters (header, separator, delimiter). [Parser.Parser]";
				$this->errlog	.= "[".date("d-m-o H:i:s")."] ".$this->message."\n";
				$this->status	= FALSE;
			}
		}


		//////////////////////////////////////////////////////////////////////////////////////////
		//																						//
		//									PRIVATE METHODS										//
		//																						//
		//////////////////////////////////////////////////////////////////////////////////////////


		// Transforms a header in form of string into an array of headers.
		private function headerToArray(){
			
			$exp_delimiter = explode($this->delimiter, $this->header_string);

			foreach ($exp_delimiter as $delimited){
				
				$exp_separator = explode($this->separator, $delimited);

				foreach ($exp_separator as $field) if ($field) $this->header_array[] = $field;

			}
		}


		// Tokenization of an array of headers. Processes a compound name and set every header as wordnet entry (WN) or compund name (CN).
		private function tokenizate(){

			foreach ($this->header_array as $key => $value){

				$synset = new Synset($value, "IT", "IT");
				
				$this->header_token['token'][$key]	= $synset->status ? "WN" : "CN";
				$this->header_token['header'][$key]	= $this->header_token['token'][$key] == "CN" ? $this->processCN($value) : $value;

			}
		}


		// Processes a compound name, separating camel case words.
		private function processCN($value){

			$terms	= explode(" ", $value);
			$output = NULL;

			foreach ($terms as $term){

				$output .= " ";
			
				for ($i = 0; $i < strlen($term); $i++){
				
					if (ord($term[$i]) >= 65 && ord($term[$i]) <= 90)
						$output .= " ";
					$output .= $term[$i];

				}
			}

			return trim(strtolower($output));

		}


		// Retrieves a synset array, a categories array and a domains array.
		private function buildSynsets(){

			$divide = function (&$num, $index, $tot) {
				$num = $num / $tot;
			};

			$table_categories = $table_domains = array();
			
			foreach ($this->header_token['header'] as $key => $term){
				
				$this->synset[$key] = new Synset($term, "IT", "IT");

				if ($this->synset[$key]->status){
					$this->synset[$key]->getSynsetArray();
				}
			}

			foreach ($this->synset as $i => $syns){

				$header_categories = $header_domains = array();

				echo "<b>".($i+1).". ".$this->header_token['header'][$i]."</b>";
				echo "<table border='1' cellpadding='5'><tr><th>#</th><th>Synset</th><th>Categorie</th><th>Domini</th><th>Sources</th></tr>";
				
				foreach ($syns->synset_array as $k => $syns_arr){
					
					echo "<tr><td>".($k+1)."</td>";
					echo "<td>{".implode(", ", $syns_arr['lemma'])."}</td>";
					echo "<td>".implode(", ", $syns_arr['category'])."</td>";
					echo "<td>".implode(", ", $syns_arr['domain'])."</td>";
					echo "<td>".implode(", ", $syns_arr['source'])."</td>";
					echo "</tr>";

					for ($j = 0; $j < count($syns_arr['category']); $j++) if ($syns_arr['category'][$j]) $header_categories[] = $syns_arr['category'][$j];
					for ($j = 0; $j < count($syns_arr['domain']); $j++) if ($syns_arr['domain'][$j]) $header_domains[] = $syns_arr['domain'][$j];

				} 

				echo "</table>";

				$dist_categs[$i]['name'] = array_unique($header_categories);
				foreach ($dist_categs[$i]['name'] as $k => $row) $dist_categs[$i]['weight'][$k] = 0;
				$this->categs[$i] = array_combine($dist_categs[$i]['name'], $dist_categs[$i]['weight']);
				foreach ($header_categories as $category) if ($this->categs[$i][$category] !== NULL) $this->categs[$i][$category]++;
				array_walk($this->categs[$i], $divide, count($header_categories));

				echo "Categorie (".count($header_categories).")<br>";
				foreach ($this->categs[$i] as $key => $value) echo $key.": ".$value." (".($value * count($header_categories))." su ".count($header_categories).")<br>";
				echo "<br><br>";

				$dist_domains[$i]['name'] = array_unique($header_domains);
				foreach ($dist_domains[$i]['name'] as $k => $row) $dist_domains[$i]['weight'][$k] = 0;
				$this->domains[$i] = array_combine($dist_domains[$i]['name'], $dist_domains[$i]['weight']);
				foreach ($header_domains as $domain) if ($this->domains[$i][$domain] !== NULL) $this->domains[$i][$domain]++;
				array_walk($this->domains[$i], $divide, count($header_domains));
				
				echo "Domini (".count($header_domains).")<br>";
				foreach ($this->domains[$i] as $key => $value) echo $key.": ".$value." (".($value * count($header_domains))." su ".count($header_domains).")<br>";
				echo "<br><br>";

				$table_categories	= array_merge($table_categories, $header_categories);
				$table_domains		= array_merge($table_domains, $header_domains);

			}

			$dist_categs		=
			$dist_domains		= NULL;
			$table_categories	= array_filter($table_categories);
			$table_domains		= array_filter($table_domains);

			$dist_categs['name'] = array_unique($table_categories);
			foreach ($dist_categs['name'] as $k => $row) $dist_categs['weight'][$k] = 0;
			$this->table_categs = array_combine($dist_categs['name'], $dist_categs['weight']);
			foreach ($table_categories as $category) if ($this->table_categs[$category] !== NULL) $this->table_categs[$category]++;
			array_walk($this->table_categs, $divide, count($table_categories));

			echo "Categorie generali (".count($table_categories).")<br>";
			foreach ($this->table_categs as $key => $value) echo $key.": ".$value." (".($value * count($table_categories))." su ".count($table_categories).")<br>";
			echo "<br><br>";

			$dist_domains['name'] = array_unique($table_domains);
			foreach ($dist_domains['name'] as $k => $row) $dist_domains['weight'][$k] = 0;
			$this->table_domains = array_combine($dist_domains['name'], $dist_domains['weight']);
			foreach ($table_domains as $domain) if ($this->table_domains[$domain] !== NULL) $this->table_domains[$domain]++;
			array_walk($this->table_domains, $divide, count($table_domains));

			echo "Domini generali (".count($table_domains).")<br>";
			foreach ($this->table_domains as $key => $value) echo $key.": ".$value." (".($value * count($table_domains))." su ".count($table_domains).")<br>";
			echo "<br><br>";
		}


		private function calculateWeightBySynset(){
			
			for ($i = 0; $i < count($this->synset); $i++){

				echo "<b>Header".($i+1)."</b>";
				echo "<table border='1' cellpadding='5'><tr><th>#</th><th>Synset</th><th>Peso categ</th><th>Peso domini</th><th>Peso tot</th></tr>";
				
				for ($k = 0; $k < count($this->synset[$i]->synset_array); $k++){

					$w_categ = $w_dom = array();

					echo "<tr><td>".($k+1)."</td>";
					echo "<td>{".implode(", ", $this->synset[$i]->synset_array[$k]['lemma'])."}</td>";

					foreach ($this->synset[$i]->synset_array[$k]['category'] as $key => $category) {
						$w_categ[] = count($this->synset[$i]->synset_array[$k]['category'] > 0) ? $this->categs[$i][$category] : -1;
					}

					foreach ($this->synset[$i]->synset_array[$k]['domain'] as $domain) {
						$w_dom[] = count($this->synset[$i]->synset_array[$k]['domain'] > 0) ? $this->domains[$i][$domain] : -1;
					}

					$categ_weight	= max($w_categ);
					$domain_weight	= max($w_dom);

					echo "<td>".$categ_weight."</td>";
					echo "<td>".$domain_weight."</td>";

					$this->synset[$i]->synset_array[$k]['weight'] = ($categ_weight*$this->categ_k + $domain_weight*$this->domain_k) / 2;
					if (!$this->synset[$i]->synset_array[$k]['weight']) $this->synset[$i]->synset_array[$k]['weight'] = "?";

					echo "<td>".$this->synset[$i]->synset_array[$k]['weight']."</td></tr>";

				}

				echo "</table>";

			}
		}


		private function calculateWeightByTable(){

			$sort = function ($a, $b){
							return $b['weight'] - $a['weight'];
						};
			
			for ($i = 0; $i < count($this->synset); $i++){

				echo "<b>Header".($i+1)."</b>";
				echo "<table border='1' cellpadding='5'><tr><th>#</th><th>Synset</th><th>Peso categ</th><th>Peso domini</th><th>Peso tot</th></tr>";
				
				for ($k = 0; $k < count($this->synset[$i]->synset_array); $k++){

					$w_categ = $w_dom = array();

					foreach ($this->synset[$i]->synset_array[$k]['category'] as $key => $category) {
						$w_categ[] = count($this->synset[$i]->synset_array[$k]['category'] > 0) ? $this->table_categs[$category] : -1;
					}

					foreach ($this->synset[$i]->synset_array[$k]['domain'] as $domain) {
						$w_dom[] = count($this->synset[$i]->synset_array[$k]['domain'] > 0) ? $this->table_domains[$domain] : -1;
					}

					$categ_weight	= max($w_categ);
					$domain_weight	= max($w_dom);

					$this->synset[$i]->synset_array[$k]['weight'] = ($categ_weight*$this->categ_k + $domain_weight*$this->domain_k) / 2;
					if (!$this->synset[$i]->synset_array[$k]['weight']) $this->synset[$i]->synset_array[$k]['weight'] = "?";

					echo "<tr><td>".($k+1)."</td>";
					echo "<td>{".implode(", ", $this->synset[$i]->synset_array[$k]['lemma'])."}</td>";
					echo "<td>".$categ_weight."</td>";
					echo "<td>".$domain_weight."</td>";
					echo "<td>".$this->synset[$i]->synset_array[$k]['weight']."</td></tr>";

				}

				usort($this->synset[$i]->synset_array, $sort);

				echo "</table>";

			}
		}


		private function order(){
			


		}


		//////////////////////////////////////////////////////////////////////////////////////////
		//																						//
		//									PUBLIC METHODS										//
		//																						//
		//////////////////////////////////////////////////////////////////////////////////////////


		// Returns an HTML-compatible version of $this->errlog.
		public function HTMLizeErrlog(){
			return str_replace("\n", "<br />", $this->errlog);
		}


		public function getHeaderArray(){
			return $this->header_array;
		}


		public function getTokenArray(){
			return $this->header_token;
		}


		public function getSynset(){
			return $this->synset;
		}


		public function getDomains(){
			return $this->domains;
		}


		public function getCategs(){
			return $this->categs;
		}


	} // End class.

?>
