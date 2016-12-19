<?php
namespace Zertifizierungstool\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zertifizierungstool\Model\Pruefung;
use Zertifizierungstool\Model\User;
use Zertifizierungstool\Model\Frage;
use Zertifizierungstool\Model\Antwort;

class FrageController extends AbstractActionController {
	
	public function createAction() {
		// TODO Pr�fen ob Pr�fungstermin schon erreicht ist
		// Die Pr�fung kann dann nicht mehr bearbeitet werden
		
		// Array, das eventuelle Fehlermeldungen enth�lt
		$errors = array();
		
		// Berechtigungspr�fung
		if (!User::currentUser()->istAdmin() && !User::currentUser()->istZertifizierer()) {
			array_push($errors, "Keine Berechtigung!");
		}
		
		// Erzeugung des Frage-Objekts mit �bergabe der zugeh�rigen Pr�fungs-Id
		$pruefungid = $_REQUEST["pruefung_id"];
		
		if (empty($pruefungid)) {
			$pruefungid = $this->params()->fromRoute('id');
		}
		
		$pruefung = new Pruefung();
		
		if (!$pruefung->load($pruefungid)) {
			array_push($errors, "Fehler beim Laden der Pr�fung!");
		}
		
		if (is_bool($fragen)) {
			array_push($errors, "Fehler beim Laden der Pr�fungsfragen!");
		}

		
		if ($_REQUEST['speichernFrage']) {
			
			$frage = new Frage("",
					$_REQUEST["frage_text"],
					$_REQUEST["punkte"],
					$_REQUEST["pruefung_id"],
					$_REQUEST["frage_typ"]);
			
			if (empty($errors)) {
				if (!$frage->saveNew()) {
					array_push($errors, "Fehler beim Speichern der Frage. Bitte erneut versuchen!");
				}
			}
			if (empty($errors)) {
				switch ($_REQUEST["frage_typ"]) {
					case "TF":
						$status = 0;
						if ($_REQUEST["tf"] == "true") {
							$status = 1;
						}
					
						$antwort = new Antwort("", "", $frage->getId(), $status);
						if (!$antwort->saveNew()) {
							array_push($errors, "Fehler beim Speichern der Antwort. Bitte erneut versuchen!");
						}
					
						break;
				
					case "MC":
						$index = 1;
						while (!empty($_REQUEST["antwort_text" .$index])) {
							$status = 0;
							if ($_REQUEST["antwort_checked" .$index]) {
								$status = 1;
							}
						
							$antwort = new Antwort("",
									$_REQUEST["antwort_text" .$index],
									$frage->getId(),
									$status);
						
							if (!$antwort->saveNew()) {
								array_push($errors, "Fehler beim Speichern der Antworten. Bitte erneut versuchen!");
							}
						
							$index++;
						}
						break;
					
					default: break;
				}
			}	
		}
	
		$viewModel = new ViewModel([
				'pruefung' => array($pruefung),
				'fragen'   => Frage::loadList($pruefung->getId()),
				'errors'   => $errors,
				'mode'	   => array(PruefungController::createFragen)
		]);
		
		$viewModel->setTemplate(PruefungController::pathToHtml);
		return $viewModel;
	}
	
	public function editAction() {
		// TODO Pr�fen ob Pr�fungstermin schon erreicht ist
		// Die Pr�fung kann dann nicht mehr bearbeitet werden
		
		// Array, das eventuelle Fehlermeldungen enth�lt
		$errors = array();
		
		// Berechtigungspr�fung
		if (!User::currentUser()->istAdmin() && !User::currentUser()->istZertifizierer()) {
			array_push($errors, "Keine Berechtigung!");
		}
		
		// Erzeugung des Frage-Objekts mit �bergabe der zugeh�rigen Pr�fungs-Id
		$frageid = $_REQUEST["id"];
		
		if (empty($frageid)) {
			$frageid = $this->params()->fromRoute('id');
		}
		
		$frage = new Frage();
		$frage->load($frageid);
		
		$pruefung = new Pruefung();		
		if (!$pruefung->load($frage->getPruefungId())) {
			array_push($errors, "Fehler beim Laden der Pr�fung!");
		}		
		
		if ($_REQUEST['speichernFrage']) {
				
			$frage = new Frage(
					$_REQUEST["id"],
					$_REQUEST["frage_text"],
					$_REQUEST["punkte"],
					$_REQUEST["pruefung_id"],
					$_REQUEST["frage_typ"]);
				
			if (empty($errors)) {
				if (!$frage->update()) {
					array_push($errors, "Fehler beim Speichern der Frage. Bitte erneut versuchen!");
				}
			}
			if (empty($errors)) {
				switch ($_REQUEST["frage_typ"]) {
					case "TF":
						$status = 0;
						if ($_REQUEST["tf"] == "true") {
							$status = 1;
						}
							
						$antwort = new Antwort("", "", $frage->getId(), $status);
						if (empty($_REQUEST["id"])) {
							if (!$antwort->saveNew()) {
								array_push($errors, "Fehler beim Speichern der Antwort. Bitte erneut versuchen!");
							}
						} else {
							if (!$antwort->update()) {
								array_push($errors, "Fehler beim Speichern der Antwort. Bitte erneut versuchen!");
							}
						}
						
							
						break;
		
					case "MC":
						$index = 1;
						while (!empty($_REQUEST["antwort_text" .$index])) {
							$status = 0;
							if ($_REQUEST["antwort_checked" .$index]) {
								$status = 1;
							}
		
							$antwort = new Antwort("",
									$_REQUEST["antwort_text" .$index],
									$frage->getId(),
									$status);
		
							if (empty($_REQUEST["id"])) {
								if (!$antwort->saveNew()) {
									array_push($errors, "Fehler beim Speichern der Antworten. Bitte erneut versuchen!");
								}
							} else {
								if (!$antwort->update()) {
									array_push($errors, "Fehler beim Speichern der Antworten. Bitte erneut versuchen!");
								}
							}
		
							$index++;
						}
						break;
							
					default: break;
				}
			}
		}
		
		$viewModel = new ViewModel([
				'pruefung' => array($pruefung),
				'fragen'   => Frage::loadList($pruefung->getId()),
				'errors'   => $errors,
				// Fragen laden -> Was bei Fehler?
				'mode'	   => array(PruefungController::editFragen),
				'frageToEdit' => array($frage)
		]);
		
		$viewModel->setTemplate(PruefungController::pathToHtml);
		return $viewModel;
	}
	
	public function deleteAction() {
		// TODO Pr�fen ob Pr�fungstermin schon erreicht ist
		// Die Pr�fung kann dann nicht mehr bearbeitet werden
		$frage_id_toDelete = $this->params()->fromRoute('id');
		$frage = new Frage();
		$frage->load($frage_id_toDelete);
		
		$antwortenToDelete = Antwort::loadList($frage_id_toDelete);
		
		foreach ($antwortenToDelete as $antwort) {
			Antwort::delete($antwort->getId());
			// TODO Fehler abfangen
		}
		
		Frage::delete($frage_id_toDelete);
		// TODO Fehler abfangen
		
		header ("refresh:0; url = /frage/create/" .$frage->getPruefungId());
	}
}