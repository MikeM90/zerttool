<?php
namespace Zertifizierungstool\Model;

use Zertifizierungstool\Model\Db_connection;

/**
 * Objekte dieser Klasse repr�sentieren die Entit�t "Pr�fung" aus der Datenbank.
 * Die Klasse enth�lt Methoden zum Lesen, Ver�ndern und L�schen von Datens�tzen aus der Tabelle "pruefung"
 * 
 * @author Martin
 *
 */
class Pruefung {
	
	/** Tabellenfeld "pruefung_id" */
	private $id;		
	private $name;
	private $termin;
	private $kurs_id;
	private $cutscore;
	
	public function __construct($id = "", $name = "", $termin = "", $kursid = "", $cutscore = "") {
		$this->id		= $id;
		$this->name		= $name;
		$this->termin	= $termin;
		$this->kurs_id  = $kursid;
		$this->cutscore = $cutscore;
	}
	
	/**
	 * F�gt die Daten des aktuellen Objekts als neuen Datensatz in der Datenbank.
	 * Setzt auch die Id des Objekts mit dem Wert, der von der DB automatisch zugeteilt wurde.
	 * 
	 * @return boolean false, falls ein Fehler aufgetreten ist. Sonst true.
	 */
	public function saveNew() {
		$db = new Db_connection();
		$conn = $db->getConnection();
		
		$query = "INSERT INTO pruefung (pruefung_name, pruefung_ab, kurs_id, cutscore) VALUES ('"
					.$this->name	. "', '"
					.$this->termin 	. "', "
					.$this->kurs_id . ", '"
					.$this->cutscore . "')" ;
		
		$result = mysqli_query($conn, $query);
				
		if(!$result) {
			// Fehler bei der Datenbankabfrage
			return false;
			
		} else {
			// Id des eben eingef�gten Datensatzes auslesen und im Objekt setzen
			$this->id = mysqli_insert_id($conn);
			return true;
		}
	}
	
	/**
	 * Aktualisiert den Datensatz mit der ID des aktuellen Objekts.
	 * 
	 * @return boolean false, falls ein Fehler aufgetreten ist. Sonst true.
	 */
	public function update() {
		$db = new Db_connection();
		$conn = $db->getConnection();
		
		$query = "UPDATE pruefung SET"
					." pruefung_name = '" .$this->name ."'"
					.", pruefung_ab = '"   .$this->termin ."'"
					.", cutscore = "      .$this->cutscore
		
				." WHERE pruefung_id = " .$this->id;
		
		$result = mysqli_query($conn, $query);
		
		if (is_bool($result) && $result == false) {
			echo $query;
			echo '<br>' .mysqli_error($conn);
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * Bef�llt die Attribute des aktuellen Objekts mit den entsprechenden Daten aus der Datenbank.
	 * 
	 * @param $id Die id des Eintrags in der Datenbank, der geladen werden soll.
	 * @return boolean false, falls ein Fehler aufgetreten ist. Sonst true.
	 */
	public function load($id) {
		$db = new Db_connection();
		
		$query = "SELECT * FROM pruefung WHERE pruefung_id = " .$id;
		
		$result = $db->execute($query);
		
		if(!$result || mysqli_num_rows($result) != 1) {
			// Fehler bei der Datenbankabfrage oder keine Pr�fung mit der Id gefunden
			return false;
		}
			
		$row = mysqli_fetch_assoc($result);
		
		$this->id		= $id;
		$this->name 	= $row["pruefung_name"];
		$this->termin   = $row["pruefung_ab"];
		$this->kurs_id  = $row["kurs_id"];
		$this->cutscore = $row["cutscore"];
		
		return true;
	}
	
	/**
	 * L�dt alle Pr�fungen, die zu einem bestimmten Kurs geh�ren und speichert diese in einem Array.
	 * 
	 * @param $kurs_id Id des Kurses, dessen Pr�fungen geladen werden sollen.
	 * @return boolean false, falls ein Fehler aufgetreten ist. Sonst das bef�llte Array.
	 */
	public static function loadList($kurs_id) {
		$db = new Db_connection();
		$conn = $db->getConnection();
	
		$query = "SELECT * FROM pruefung WHERE kurs_id = " .$kurs_id;
	
		$result = mysqli_query($conn, $query);
	
		if(!$result) {
			// Fehler bei der Datenbankabfrage
			return false;
	
		} else {
			$return_array = array();
			while ($row = mysqli_fetch_assoc($result)) {
				$p = new Pruefung(
						$row["pruefung_id"],
						$row["pruefung_name"],
						$row["pruefung_ab"],
						$row["kurs_id"],
						$row["cutscore"]);
	
				array_push($return_array, $p);
			}
	
			return $return_array;
		}
	}
	
	
	// Getter methods
	public function getId() 	  {return $this->id;}	
	public function getName() 	  {return $this->name;}
	public function getTermin()   {return $this->termin;}
	public function getKursId()   {return $this->kurs_id;}
	public function getCutscore() {return $this->cutscore;}
	
	
	// Setter methods
	public function setId($id) {
		$this->id = $id;
	}
	
	public function setName($name) {
		$this->name = $name;
	}
	
	public function setTermin($termin) {
		$this->termin = $termin;
	}
	
	public function setKursId($kursId) {
		$this->kurs_id = $kursId;
	}
	
	public function setCutscore($cutscore) {
		$this->cutscore = $cutscore;
	}

}