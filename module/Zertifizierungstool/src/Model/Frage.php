<?php
namespace Zertifizierungstool\Model;

use Zertifizierungstool\Model\Db_connection;

class Frage {
	
	private $id;
	private $text;
	private $punkte;
	private $pruefung_id;
	
	public function __construct($id = "", $text = "", $punkte = "", $pruefung_id = "" ) {
		$this->id 		   = $id;
		$this->text  	   = $text;
		$this->punkte 	   = $punkte;
		$this->pruefung_id = $pruefung_id;
	}
	public function saveNew() {
		$db = new Db_connection();
		
		$query = "INSERT INTO frage (frage_id, frage_text, punkte, pruefung_id) VALUES ("
				.$this->id	. ", '"
				.$this->text 	. "', "
				.$this->punkte . ", "
				.$this->pruefung_id . ")" ;
		
		$result = $db->execute($query);
		
		// TODO fehler
	}
}