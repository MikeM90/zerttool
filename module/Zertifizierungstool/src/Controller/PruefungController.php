<?php
namespace Zertifizierungstool\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zertifizierungstool\Model\Pruefung;
use Zertifizierungstool\Model\Kurs;

/**
 * TODO Dokumentation
 * @author martin
 *
 */
class PruefungController extends AbstractActionController {
	
	function anlegenAction() {
		// TODO Berechtigungspr�fung
		

		
		$pruefung = new Pruefung($this->params()->fromRoute('id'));
		print_r($_REQUEST);
		
		
		if ($_REQUEST['speichern']) {
			// Array, das eventuelle Fehlermeldungen enth�lt
			$errors = array();
			
			// Pruefung-Objekt mit Daten aus Request-Array f�llen
			$pruefung->setId($_REQUEST["id"]);
			$pruefung->setName($_REQUEST["name"]);
			$pruefung->setTermin($_REQUEST["termin"]);
			$pruefung->setCutscore($_REQUEST["cutscore"]);
			$pruefung->setKursId($_REQUEST["kursid"]);
			
			/*
			// Termin der Pr�fung muss nach Kursbeginn liegen und mindestens 4 Tage vor Kursende
				// Kurs laden, zu dem die Pr�fung geh�rt	
				$kurs = new Kurs();
				if (!$kurs->laden($pruefung->getKursId())) {
					// Fehlermeldung: Der Kurs wurde nicht in der Datenbank gefunden
				}
				
				// Termin �berpr�fen
				if ($pruefung->getTermin() < $kurs->getKurs_start()) {
					array_push($errors, "Der Pr�fungszeitraum kann erst nach Kursbeginn beginnen!");
					
				}elseif ($pruefung->getTermin() > date_sub($kurs->getKurs_start(), new \DateInterval("P4D"))) {
					array_push($errors, "Der Pr�fungszeitraum muss sp�testens 4 Tage vor Kursende beginnen!");
				}
				*/
			
				
				$pruefung->anlegen();
			// Falls keine Fehler => FrageController->anlegenAction() mit Parameter Pr�fungs-Id;
		}
		
		
		return new ViewModel([
				'pruefung' => array($pruefung),
				//'errors'   => $errors
		]);
	}
}