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
	const createPruefung = "Pruefung anlegen";
	const editPruefung   = "Pruefung bearbeiten";
	const createFragen   = "Fragen anlegen";
	const editFragen	 = "Fragen bearbeiten";
	
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
				'pruefung' => array($pruefung),
				'errors'   => $errors,
				'result'   => array($result),
				'fragen'   => $fragen,
				'mode'	   => array(PruefungController::createPruefung)
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
			// TODO austesten: return "Keine Berechtigung!";
			array_push($errors, "Keine Berechtigung!");
		}
		
		$pruefung_id = $_REQUEST["pruefid"];
		
		if (empty($pruefung_id)) {
			$pruefung_id = $this->params()->fromRoute('id');
		}
		
		$pruefung = new Pruefung();
		$pruefung->load($pruefung_id);
		
		if ($_REQUEST['speichernPruefung']) {
				
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
					
					$result = true;
				}
			}
		}
			
		$viewModel = new ViewModel([
				'pruefung' => array($pruefung),
				'errors'   => $errors,
				'result'   => array($result),
				'fragen'   => Frage::loadList($pruefung->getId()),
				'mode'	   => array(PruefungController::editPruefung)
		]);
		
		
		$viewModel->setTemplate(PruefungController::pathToHtml);
		return $viewModel;
	}
	
	//TODO
	public function deleteAction() {
		
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