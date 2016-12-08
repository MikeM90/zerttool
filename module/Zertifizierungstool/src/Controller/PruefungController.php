<?php
namespace Zertifizierungstool\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zertifizierungstool\Model\Pruefung;
use Zertifizierungstool\Model\Kurs;
use Zertifizierungstool\Model\User;

/**
 * TODO Dokumentation
 * @author martin
 *
 */
class PruefungController extends AbstractActionController {
	
	public function createAction() {
		// Array, das eventuelle Fehlermeldungen enth�lt
		$errors = array();
		
		// Berechtigungspr�fung
		if (!User::currentUser()->istAdmin() && !User::currentUser()->istZertifizierer()) {
			array_push($errors, "Keine Berechtigung!");
			return;
		}

		// Erzeugung des Pr�fungs-Objekts mit �bergabe der zugeh�rigen Kurs-Id
		$pruefung = new Pruefung($kursid = $this->params()->fromRoute('id'));		
		
		if ($_REQUEST['speichern']) {
			
			$pruefung = new Pruefung("", 
					$_REQUEST["name"],
					$_REQUEST["termin"], 
					$_REQUEST["kursid"], 
					$_REQUEST["cutscore"] );
			
			// TODO Format des Pr�fungstermins �berpr�fen
			// Pr�fungstermin validieren
			//array_push($errors, $this->checkDate($pruefung));
			
			if (empty($errors)) {
				if (!$pruefung->saveNew()) {
					array_push($errors, "Fehler beim Speichern der Pr�fung. Bitte erneut versuchen!");
				}else {
					// FrageController->anlegenAction() mit Parameter Pr�fungs-Id;
					// oder hier createQuestionAction()
				}
			}
		}
			
		return new ViewModel([
				'pruefung' => array($pruefung),
				'errors'   => $errors
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
	private function checkDate($pruefung) {
		$error;
		$kurs = new Kurs();
		
		if ($kurs->laden($pruefung->getKursId())) {
			if ($pruefung->getTermin() < $kurs->getKurs_start()) {
				$error= "Der Pr�fungszeitraum kann erst nach Kursbeginn beginnen!";
			
			}elseif ($pruefung->getTermin() > date_sub($kurs->getKurs_start(), new \DateInterval("P4D"))) {
				$error = "Der Pr�fungszeitraum muss sp�testens 4 Tage vor Kursende beginnen!";
			}
		}else {
			$error = "Der Kurs wurde nicht in der Datenbank gefunden!";
		}
		
		return $error;
	}
}