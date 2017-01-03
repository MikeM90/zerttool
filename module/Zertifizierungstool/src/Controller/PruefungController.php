<?php
namespace Zertifizierungstool\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zertifizierungstool\Model\Pruefung;
use Zertifizierungstool\Model\Kurs;
use Zertifizierungstool\Model\User;
use Zertifizierungstool\Model\Frage;
use Zertifizierungstool\Model\Antwort;
use Zertifizierungstool\Model\Schreibt_pruefung;
use Zertifizierungstool\Model\Beantwortet;
use Zertifizierungstool\Model\SchreibtPruefung;

/**
 * Controller, der Aufgaben verarbeitet, die sich auf die Entit�t "Pr�fung" beziehen.
 * Beinhaltet Actions zum Anlegen, Bearbeiten, Absolvieren und L�schen von Pr�fungen.
 * 
 * @author martin
 */
class PruefungController extends AbstractActionController {
	
	const pathToHtml	 = 'zertifizierungstool/pruefung/pruefung';
	
	const PRUEFUNG 		 = "Pruefung";
	const createFragen   = "Fragen anlegen";
	const editFragen	 = "Fragen bearbeiten";
	
	/** Das behandelte Pr�fungs-Objekt */
	private $pruefung;
	
	public function takeAction() {
		// TODO Pr�fen ob Teilnehmer im Kurs eingetragen ist
		// TODO Pr�fen, ob Kursende schon erreicht
		// TODO Pr�fen, ob letzter Versuch schon 24 Stunden her ist
		// TODO Pr�fen, ob Teilnehmer die Pr�fung schon bestanden hat oder 3 mal nicht bestanden hat
		
		$errors = array();
		
		// Pr�fungs-Id aus URL laden
		$pruefung_id = $this->params()->fromRoute('id');

		// Eintrag in Tabelle schreibt_pruefung
		$schreibt_pruefung = new SchreibtPruefung("", $pruefung_id);
		
		if (!$schreibt_pruefung->saveNew()) {
			array_push($errors, "Fehler beim Vorbereiten der Pr�fung!");
		}
		
		// Alle Fragen zur Pr�fung laden
		$fragen = Frage::loadList($pruefung_id);
		// TODO Fehler
		
		// F�r jede Frage:
		foreach ($fragen as $frage) {
			// Alle Antworten laden
			$antworten = Antwort::loadList($frage->getId());
			
			// F�r jede Antwort:
			foreach ($antworten as $antwort) {
				// Objekt von "beantwortet" erzeugen mit schreibt_pruefung->getId(), antwort->getId(), beantwortet_status = 0 ->in Db speichern
				// extra-Attribut "edited"? (gesetzt sobal User auf "Weiter" oder so geklickt hat)
				$beantwortet = new Beantwortet("", $schreibt_pruefung->getId(), $antwort->getId(), 0);
				if (!$beantwortet->saveNew()) {
					array_push($errors, "Fehler beim Vorbereiten der Pr�fungsfragen!");
				}
			}
		}
		
		if (empty($errors)) {
			// Weiterleiten an FrageController Action answer
			header("refresh:0; url = /frage/answer/" .$schreibt_pruefung->getId());
		}

		return new ViewModel(['errors' => $errors]);
	}
	
	/**
	 * �berpr�ft, ob ein Teilnehmer eine Pr�fung bestanden hat
	 */
	public function resultAction() {
		// aus schreibt_pruefung auslesen
			// id aus route
		// Abgebene Antworten pr�fen und evtl "bestanden" in schreibt_pruefung auf 1 setzen
		// Mit cutscore vergleichen
		// Anbieten Zertifikat runterzuladen
	}
	
	/**
	 * Verarbeitet das Formular zum Anlegen und Bearbeiten von Pr�fungen
	 * @param $request Daten aus Request-Array
	 * @param array $fragen Evtl. bereits angelegte Fragen
	 * @return \Zend\View\Model\ViewModel
	 */
	private function handleForm($request, $fragen = array()) {
		// Array, das eventuelle Fehlermeldungen enth�lt
		$errors = array();
		
		if (isset($request['speichernPruefung'])) {
		
		$this->pruefung = new Pruefung(
				$request["pruefid"],
				$request["name"],
				$request["termin"],
				$request["kursid"],
				$request["cutscore"] / 100 );
		
		// TODO Format des Pr�fungstermins �berpr�fen
		// Pr�fungstermin validieren
		//array_push($errors, $this->checkDate());
		$kurs = new Kurs();
		
		if ($kurs->load($this->pruefung->getKursId())) {
			$minus = new \DateTime();
			//$minus->setTimestamp(strtotime($kurs->getKurs_ende()));
			
			array_push($errors, "Ende: " .strftime('Y-m-d', $minus));
			$minus->modify('-4 days');
			array_push($errors, "Ende - 4: " .strftime('Y-m-d', $minus));
			
			if ($this->pruefung->getTermin() < $kurs->getKurs_start()) {
				array_push($errors, "Der Pr&uuml;fungszeitraum kann erst nach Kursbeginn starten!");
					
			}elseif ($this->pruefung->getTermin() > date_sub(new \DateTime($kurs->getKurs_ende()), new \DateInterval("P4D"))) {
				array_push($errors, "Der Pr&uuml;fungszeitraum muss mindestens 4 Tage vor Kursende starten!");
			}
		}else {
			array_push($errors, "Der Kurs wurde nicht in der Datenbank gefunden!");
		}
			
		if (empty($errors)) {
			if ($this->pruefung->save()) {
				header ("refresh:0; url = /frage/create/" .$this->pruefung->getId());
			}else {
				array_push($errors, "Fehler beim Speichern der Pr&uuml;fung. Bitte erneut versuchen!");
			}
		}
		}

		$viewModel = new ViewModel([
				'pruefung' => $this->pruefung,
				'errors'   => $errors,
				'fragen'   => $fragen,
				'mode'	   => PruefungController::PRUEFUNG
		]);
		
		
		$viewModel->setTemplate(PruefungController::pathToHtml);
		return $viewModel;
	}
	
	/**
	 * Legt die Kopfdaten einer neuen Pr�fung in der Datenbank an.
	 * @return \Zend\View\Model\ViewModel
	 */
	public function createAction() {
		// Berechtigungspr�fung TODO weiterleitung auf fehlerseite
		if (!User::currentUser()->istAdmin() && !User::currentUser()->istZertifizierer()) {
			header ("refresh:0; url = /user/login/");
		}
		
		if (empty($_REQUEST["kursid"])) {
			$newKursid = $this->params()->fromRoute('id');	
		}else {
			$newKursid = $_REQUEST["kursid"];
		}
		
		$this->pruefung = new Pruefung();
		$this->pruefung->setKursId($newKursid);
		
		return $this->handleForm($_REQUEST);
	}
	
	/**
	 * Bearbeitet die Kopfdaten einer Pr�fung.
	 * @return \Zend\View\Model\ViewModel
	 */
	public function editAction() {
		// Berechtigungspr�fung TODO weiterleitung auf fehlerseite
		if (!User::currentUser()->istAdmin() && !User::currentUser()->istZertifizierer()) {
			header ("refresh:0; url = /user/login/");
		}
		
		if (empty($_REQUEST["pruefid"])) {
			$pruefung_id = $this->params()->fromRoute('id');
		}else {
			$pruefung_id = $_REQUEST["pruefid"];
		}
		
		$this->pruefung = new Pruefung();
		$this->pruefung->load($pruefung_id);
		
		$kurs = new Kurs();
		$kurs->load($this->pruefung->getKursId());
		
		if (!$kurs->getBenutzername() == User::currentUser()->getBenutzername()) {
			header ("refresh:0; url = /user/login/");
		}
		
		// Was wenn Fehler? Zur�ckleiten auf �bersicht?
		if ($this->pruefung->getTermin() <= time()) {
			array_push($errors, "Der Pr�fungszeitraum wurde bereits erreicht. Die Pr�fung kann nicht mehr bearbeitet werden!");
		}
		
		return $this->handleForm($_REQUEST, Frage::loadList($this->pruefung->getId()));
	}
	
	
	
	//TODO
	/**
	 * L�scht eine Pr�fung.
	 */
	public function deleteAction() {
		
	}
	
	/**
	 * Listet alle Pr�fungen auf, die zu einem Kurs geh�ren
	 */
	public function overviewAction() {
		$kursid = $this->params()->fromRoute('id');
		$pruefungen = Pruefung::loadList($kursid);
		
		if ($pruefungen == false) {
			// Fehler
		}
		
		return new ViewModel([
				'pruefungen' => $pruefungen,
				'kursid'	 => $kursid
		]);
	}
	
	/**
	 * �berpr�ft den Pr�fungstermin nach folgenden Kriterien:
	 *  - nach Kursbeginn
	 *  - mindestens 4 Tage vor Kursende
	 *  
	 * @param Die zu �berpr�fende Pr�fung $pruefung
	 * @return Eventuelle Fehlermeldung
	 */
	private function checkDate() {
		$error;
		$kurs = new Kurs();
		
		if ($kurs->load($this->pruefung->getKursId())) {
			if ($this->pruefung->getTermin() < $kurs->getKurs_start()) {
				$error= "Der Pr&uuml;fungszeitraum kann erst nach Kursbeginn starten!";
			
			}elseif ($this->pruefung->getTermin() > date_sub(new \DateTime($kurs->getKurs_ende()), new \DateInterval("P4D"))) {
				echo "Kursende: " .strtotime($kurs->getKurs_ende());
				echo "<br>Kursende ohne strtotime: " .$kurs->getKurs_ende();
				echo "<br>Kursende mit DateTime: " .new \DateTime(strtotime($kurs->getKurs_ende()));
				echo "<br>Minus 4 Tage: " .date_sub(new \DateTime($kurs->getKurs_ende()), new \DateInterval("P4D"));
				$error = "Der Pr&uuml;fungszeitraum muss mindestens 4 Tage vor Kursende starten!";
			}
		}else {
			$error = "Der Kurs wurde nicht in der Datenbank gefunden!";
		}
		
		return $error;
	}
}