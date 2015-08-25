<?php
//  (c) Thomas Konings - a.k.a. tkon99  //
//      API-gepro-osi, complete API     //
include("simple_html_dom.php");

class rooster{
	private $school = 507;
	private $hours = 10;

	private $cells = 6; //Amount of cells per hour

	public function __construct($school = 507, $hours = 10){
		$this->school = $school;
		$this->hours = $hours;
		return true;
	}

	/*
	*	Generic functions, no parameters required
	*/

	public function getLastEdit(){
		$url = "http://roosters5.gepro-osi.nl/roosters/rooster.php?school=".$this->school."&wijzigingen=1&type=Klasrooster";
		$html = file_get_html($url);
		$datum = $html->find('font[class=fntprompt]');
		$datum = explode(' ', str_replace("&nbsp;", "", $datum[0]->plaintext));
		array_shift($datum); array_shift($datum);
		$date = new DateTime(implode(' ', $datum), new DateTimeZone('Europe/Amsterdam'));
		return $date;
	}

	public function getGroups(){ // Klassen
		$url = "http://roosters5.gepro-osi.nl/roosters/rooster.php?school=".$this->school."&wijzigingen=1&type=Klasrooster";
		$html = file_get_html($url);
		$return = array();
		foreach($html->find('option') as $klas){
			$return[] = $klas->plaintext;
		}
		return $return;
	}

	public function getTeachers(){ // Leraren
		$url = "http://roosters5.gepro-osi.nl/roosters/rooster.php?school=".$this->school."&wijzigingen=1&type=Docentrooster";
		$html = file_get_html($url);
		$return = array();
		foreach($html->find('option') as $docent){
			$return[] = $docent->plaintext;
		}
		return $return;
	}

	public function getRooms(){ // Lokalen
		$url = "http://roosters5.gepro-osi.nl/roosters/rooster.php?school=".$this->school."&wijzigingen=1&type=Lokaalrooster";
		$html = file_get_html($url);
		$return = array();
		foreach($html->find('option') as $lokaal){
			$return[] = $lokaal->plaintext;
		}
		return $return;
	}

	public function getSections(){ // Richtingen (e.g. Vwo 4)
		$url = "http://roosters5.gepro-osi.nl/roosters/rooster.php?school=".$this->school."&wijzigingen=1&type=Leerlingrooster";
		$html = file_get_html($url);
		$return = array();
		foreach($html->find('option') as $richting){
			$return[] = $richting->plaintext;
		}
		return $return;
	}

	public function getNotes(){ // Geeft notities onder het rooster terug
		$url = "http://roosters5.gepro-osi.nl/roosters/rooster.php?school=".$this->school;
		$html = file_get_html($url);
		$notes = $html->find(".Remark");
		return trim(preg_replace('/(<br>)+$/', '', $notes[0]->innertext)); // Remove line breaks at the end
	}

	/*
	*	Student functions
	*/

	public function getStudents($section){ // Leerlingen uit bepaalde richting
		$url = "http://roosters5.gepro-osi.nl/roosters/rooster.php?school=".$this->school."&wijzigingen=1&type=Leerlingrooster&afdeling=".urlencode($section);
		$html = file_get_html($url);
		$students = array();
		$numbers = array();
		foreach($html->find('option') as $leerling){
			$students[] = $leerling->plaintext;
			$numbers[] = $leerling->value;
		}
		$return = array();
		$return[] = $students;
		$return[] = $numbers;
		return $return;
	}

	// Used to strip groups from student list
	public function getNameList($name){ // Special to Norbertuscollege
		$parts = explode(" ", $name);
		$klas = array_shift($parts);
		array_shift($parts);
		return trim(implode(" ", $parts));
	}

	// Used to strip groups from name on schedule
	public function getNameSchedule($name){ // Special to Norbertuscollege
		$parts = explode(" ", $name);
		$klas = array_pop($parts);
		array_pop($parts);
		return trim(implode(" ", $parts));
	}

	/*
	*	Schedule functions
	*/

	//Type: Leerling
	//		Klas
	//		Docent
	//		Lokaal
	public function getUrl($type = "leerling", $second = false, $third = false){
		if($second == false && $third == false){
			throw new Exception("getUrl needs more parameters.", 1);
		}else{
			if(strtolower($type) == "leerling"){
				if($second !== false && $third !== false){
					// Second = section
					// Third = number (id)
					return "http://roosters5.gepro-osi.nl/roosters/rooster.php?school=".$this->school."&wijzigingen=1&type=Leerlingrooster&afdeling=".urlencode($second)."&leerling=".urlencode($third);
				}else{
					throw new Exception("getUrl needs more parameters.", 1);	
				}
			}else if(strtolower($type) == "klas"){
				if($second !== false){
					// Second = group
					return "http://roosters5.gepro-osi.nl/roosters/rooster.php?type=Klasrooster&wijzigingen=1&school=".$this->school."&klassen%5B%5D=".urlencode($second);
				}else{
					throw new Exception("getUrl needs more parameters.", 1);	
				}
			}else if(strtolower($type) == "docent"){
				if($second !== false){
					// Second = teacher code
					return "http://roosters5.gepro-osi.nl/roosters/rooster.php?type=Docentrooster&wijzigingen=1&school=".$this->school."&docenten%5B%5D=".urlencode($second);
				}else{
					throw new Exception("getUrl needs more parameters.", 1);
				}
			}else if(strtolower($type) == "lokaal"){
				if($second !== false){
					// Second = room id
					return "http://roosters5.gepro-osi.nl/roosters/rooster.php?type=Lokaalrooster&wijzigingen=1&school=".$this->school."&lokalen%5B%5D=".urlencode($second);
				}else{
					throw new Exception("getUrl needs more parameters.", 1);	
				}
			}else{
				throw new Exception("getUrl needs a valid type as it's first parameter.", 1);
			}
		}
	}

	public function getName($url){
		$html = file_get_html($url);
		$naam = $html->find('.lNameHeader');
		return $this->getNameSchedule($naam[0]->plaintext);
	}

	public function getSchedule($url){
		$html = file_get_html($url);
		$roostertable = $html->find('tr td[class=tableCell] table');

		$table = array();
		for($x = 0; $x < 5; $x++){
			$table[$x] = array();
			for($i = $x; $i < $this->hours*5; $i=$i+5){
				$hour = floor($i/5);

				$uur = $roostertable[$i];
				$data = $uur->find('tr td');

				$hours = count($data)/$this->cells;

				for($h = 1; $h <= $hours; $h++){
					$maxIndex = $h*$this->cells;

					$leraar = $data[$maxIndex - $this->cells];
					$lokaal = $data[$maxIndex - $this->cells + 2];
					$vak = $data[$maxIndex - $this->cells + 4];
					$cluster = $data[$maxIndex - $this->cells + 5];

					$class = $leraar->class;
					$status_txt = "";
					$status = 0;					//1: wijziging, 2: uitval, 0: normaal
					if($class == 'tableCellNew'){
						$status_txt = 'wijziging';
						$status = 1;
					}else if($class == 'tableCellRemoved'){
						$status_txt = 'uitval';
						$status = 2;
					}else{
						$status_txt = 'normaal';
					}

					$table[$x][$hour][$h-1] = array(
					"vak"=>$vak->plaintext,
					"lokaal"=>$lokaal->plaintext,
					"leraar"=>$leraar->plaintext,
					"cluster"=>$cluster->plaintext,
					"status"=>array(
						"type"=>$status,
						"text"=>$status_txt
						)
					);

				}

			}
		}

		return $table;
	}

	/*
	*	Extra functionality functions (swag)
	*/

	// This basicaly brute forces all sections until a name is found
	public function getSectionFromNumber($number = false){
		if($number == false){
			throw new Exception("getSectionFromNumber needs a number as it's parameter.", 1);
		}else{
			$sections = $this->getSections();
			$found = false;
			foreach($sections as $section){
				$naam = $this->getName($this->getUrl("leerling", $section, $number));
				if(!empty($naam)){
					$found = $section;
					break;
				}
			}
			return $found;
		}
	}
}
?>