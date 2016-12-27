<?php
namespace Zertifizierungstool\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zertifizierungstool\Model\Pruefung;
use Zertifizierungstool\Model\Kurs;
use Zertifizierungstool\Model\User;
use Zertifizierungstool\Model\Frage;

/**
 * TODO Dokumentation
 * @author martin
 *
 */
class PruefungController extends AbstractActionController {
	
	const pathToHtml	 = 'zertifizierungstool/pruefung/pruefung';
	
	// TODO Pr�fungskonstanten zusammenlegen?
	//const createPruefung = "Pruefung anlegen";
	//const editPruefung   = "Pruefung bearbeiten";
	const PRUEFUNG 		 = "Pruefung";
	const createFragen   = "Fragen anlegen";
	const editFragen	 = "Fragen bearbeiten";
	
	/** Das behandelte Pr�fungs-Objekt */
	private $pruefung;
	
	private function handleForm($request, $fragen = array()) {
		// Array, das eventuelle Fehlermeldungen enth�lt
		$errors = array();
		
		if ($request['speichernPruefung']) {
		
		$this->$pruefung = new Pruefung(
				$request["pruefid"],
				$request["name"],
				$request["termin"],
				$request["kursid"],
				$request["cutscore"] / 100 );
			
		// TODO Format des Pr�fungstermins �berpr�fen
		// Pr�fungstermin validieren
		//array_push($errors, $this->checkDate($pruefung));
			
		if (empty($errors)) {
			if (empty($request["pruefid"])) {
				$success = $this->$pruefung->saveNew();
			}else {
				$success = $this->$pruefung->update();
			}
			
			if ($success) {
				header ("refresh:0; url = /frage/create/" .$this->$pruefung->getId());
			}else {
				array_push($errors, "Fehler beim Speichern der Pr&uuml;fung. Bitte erneut versuchen!");
			}
		}
		}

		$viewModel = new ViewModel([
				'pruefung' => $this->$pruefung,
				'errors'   => $errors,
				'fragen'   => $fragen,
				'mode'	   => PruefungController::createPruefung
		]);
		
		
		$viewModel->setTemplate(PruefungController::pathToHtml);
		return $viewModel;
	}
	
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
		
		$this->$pruefung = new Pruefung();
		$this->$pruefung->setKursId($newKursid);
		
		return $this->handleForm($_REQUEST[]);
	}
	
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
		
		$this->$pruefung = new Pruefung();
		$this->$pruefung->load($pruefung_id);
		
		$kurs = new Kurs();
		$kurs->load($this->$pruefung->getKursId());
		
		if (!$kurs->getBenutzername() == User::currentUser()->getBenutzername()) {
			header ("refresh:0; url = /user/login/");
		}
		
		if ($this->$pruefung->getTermin() >= new Date()) {
			array_push($errors, "Der Pr�fungszeitraum wurde bereits erreicht. Die Pr�fung kann nicht mehr bearbeitet werden!");
		}
		
		return $this->handleForm($_REQUEST[], Frage::loadList($this->$pruefung->getId()));
	}
	
	/*
	public function createAction() {
		// Array, das eventuelle Fehlermeldungen enth�lt
		$errors = array();
		$result = false;
		$fragen = array();
		
		// Berechtigungspr�fung TODO weiterleitung auf fehlerseite
		if (!User::currentUser()->istAdmin() && !User::currentUser()->istZertifizierer()) {
			array_push($errors, "Keine Berechtigung!");
		}

		$newKursid = $_REQUEST["kursid"];
		
		if (empty($newKursid)) {
			$newKursid = $this->params()->fromRoute('id');
		}
		
		$pruefung = new Pruefung();
		$pruefung->setKursId($newKursid);
				
		
		if ($_REQUEST['speichernPruefung']) {
			
			$pruefung = new Pruefung("", 
					$_REQUEST["name"],
					$_REQUEST["termin"], 
					$_REQUEST["kursid"], 
					$_REQUEST["cutscore"] / 100 );
			
			// TODO Format des Pr�fungstermins �berpr�fen
			// Pr�fungstermin validieren
			//array_push($errors, $this->checkDate($pruefung));
			
			if (empty($errors)) {
				if (!$pruefung->saveNew()) {
					array_push($errors, "Fehler beim Speichern der Pr&uuml;fung. Bitte erneut versuchen!");
				}else {
					header ("refresh:0; url = /frage/create/" .$pruefung->getId());
					//$result = true;
				}
			}
		}
			
		$viewModel = new ViewModel([
				'pruefung' => $pruefung,
				'errors'   => $errors,
				'fragen'   => $fragen,
				'mode'	   => PruefungController::createPruefung
		]);
		

		$viewModel->setTemplate(PruefungController::pathToHtml);
		return $viewModel;
		
	}
	
	
	public function editAction() {
		
		// TODO Pr�fen ob Pr�fungstermin schon erreicht ist
		// Die Pr�fung kann dann nicht mehr bearbeitet werden
		
		// Array, das eventuelle Fehlermeldungen enth�lt
		$errors = array();
		$result = false;
		
		// Berechtigungspr�fung
		if (!User::currentUser()->istAdmin() && !User::currentUser()->istZertifizierer()) {
			return "Keine Berechtigung";
			//array_push($errors, "Keine Berechtigung!");
		}

		
		if (!$_REQUEST['speichernPruefung']) {
			// Formular wurde noch nicht gesendet
			$pruefung_id = $this->params()->fromRoute('id');
			$pruefung = new Pruefung();
			$pruefung->load($pruefung_id);
			$kurs = new Kurs();
			$kurs->load($pruefung->getKursId());
			
			if (!$kurs->getBenutzername() == User::currentUser()->getBenutzername()) {
				array_push($errors, "Keine Berechtigung!");
			}
			
			if ($pruefung->getTermin() >= new Date()) {
				array_push($errors, "Der Pr�fungszeitraum wurde bereits erreicht. Die Pr�fung kann nicht mehr bearbeitet werden!");
			}
			
		} else {
				
			$pruefung = new Pruefung(
					$_REQUEST["pruefid"],
					$_REQUEST["name"],
					$_REQUEST["termin"],
					$_REQUEST["kursid"],
					$_REQUEST["cutscore"] / 100 );
				
			// TODO Format des Pr�fungstermins �berpr�fen
			// Pr�fungstermin validieren
			//array_push($errors, $this->checkDate($pruefung));
				
			if (empty($errors)) {
				if (!$pruefung->update()) {
					array_push($errors, "Fehler beim Speichern der Pr&uuml;fung. Bitte erneut versuchen!");
				}else {
					header ("refresh:0; url = /frage/create/" .$pruefung->getId());
					//$result = true;
				}
			}
		}
			
		$viewModel = new ViewModel([
				'pruefung' => $pruefung,
				'errors'   => $errors,
				'fragen'   => Frage::loadList($pruefung->getId()),
				'mode'	   => PruefungController::editPruefung
		]);
		
		
		$viewModel->setTemplate(PruefungController::pathToHtml);
		return $viewModel;
	}
	*/
	
	//TODO
	public function deleteAction() {
		
	}
	
	/**
	 * Listet alle Pr�fungen auf, die zu einem Kurs geh�ren
	 */
	public function overviewAction() {
		$pruefungen = Pruefung::loadList($this->params()->fromRoute('id'));
		
		if ($pruefungen == false) {
			// Fehler
		}
		
		return new ViewModel(['pruefungen' => $pruefungen]);
	}
	
	/**
	 * �berpr�ft den Pr�fungstermin nach folgenden Kriterien:
	 *  - nach Kursbeginn
	 *  - mindestens 4 Tage vor Kursende
	 *  
	 * @param Die zu �berpr�fende Pr�fung $pruefung
	 * @return Eventuelle Fehlermeldung
	 */
	private function checkDate($pruefung) {
		$error;
		$kurs = new Kurs();
		
		if ($kurs->load($pruefung->getKursId())) {
			if ($pruefung->getTermin() < $kurs->getKurs_start()) {
				$error= "Der Pr&uuml;fungszeitraum kann erst nach Kursbeginn beginnen!";
			
			}elseif ($pruefung->getTermin() > date_sub($kurs->getKurs_ende(), new \DateInterval("P4D"))) {
				$error = "Der Pr&uuml;fungszeitraum muss sp�testens 4 Tage vor Kursende beginnen!";
			}
		}else {
			$error = "Der Kurs wurde nicht in der Datenbank gefunden!";
		}
		
		return $error;
	}
}