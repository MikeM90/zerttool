<?php
namespace Zertifizierungstool\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zertifizierungstool\Model\Pruefung;
use Zertifizierungstool\Model\User;
use Zertifizierungstool\Model\Frage;
use Zertifizierungstool\Model\Antwort;
use Zertifizierungstool\Model\Beantwortet;
use Zertifizierungstool\Model\Kurs;
use Zertifizierungstool\Model\SchreibtPruefung;

/**
 * Controller, der Aufgaben verarbeitet, die sich auf die Entit�ten "Frage" und "Antwort" bzw. "Beantwortet" beziehen.
 * Beinhaltet Actions zum Anlegen, Bearbeiten, Beantworten und L�schen von Fragen und Antworten.
 *
 * @author martin
 */
class FrageController extends AbstractActionController {
	
	private $frage;
	
	public function answerAction() {
		// Alle Fragen zur Pr�fung laden
		$schreibt_pruefung_id = $this->params()->fromRoute('id');
		$schreibt_pruefung = new SchreibtPruefung();
		$schreibt_pruefung->load($schreibt_pruefung_id);
		
		$fragen = Frage::loadList($schreibt_pruefung->getPruefungId());
		// TODO wenn leer oder Fehler
		
		// Fragen sortieren und in einem Array speichern mit der Frage-Id als Schl�ssel
		$fragen_map = array();
		
		foreach ($fragen as $frage) {
			$fragen_map[(string)$frage->getId()] = $frage;
		}
		
		ksort($fragen_map);
		
		if (isset($_REQUEST['next_id'])) {
			// 
			$current_question = $_REQUEST['next_id'];
		} else {
			// Es wurde noch keine Frage beantwortet -> Schl�ssel = Id der ersten Frage im Array
			$current_question = key($fragen_map);
		}

		echo $current_question;
		echo 'vardump:<br>';
		var_dump($fragen_map);
		
		$frage_to_answer = $fragen_map[(string)$current_question];
		
		echo '<br><br>FrageToAnswer';
		echo $frage_to_answer->getText();
		
		// Alle Antworten zu dieser Frage laden
		$antworten = Antwort::loadList($frage_to_answer->getId());
		// TODO wenn leer oder Fehler
		
		// F�r jede Antwortm�glichkeit den Eintrag aus der Tabelle 'beantwortet' laden, um die Ankreuzfelder zu bef�llen
		$beantwortete = array();
		foreach ($antworten as $antwort) {
			$beantwortet = new Beantwortet();
			$beantwortet->load($schreibt_pruefung_id, $antwort->getId());
			array_push($beantwortete, array('antwort' => $antwort, 'status' => $beantwortet->getStatus()));
		}
		
		// Ermitteln der n�chsten Frage in der Reihenfolge
		while (key($fragen_map) !== $current_question) {
			next($fragen_map);
		}
		if (!next($fragen_map)) {
			// Ende des Array wurde erreicht -> Wieder zur�ck zur ersten Frage
			reset($fragen_map);
		}
		
		// Nachdem Formular angesendet wurde:
		if ($_REQUEST['speichern']) {
			if ($_REQUEST['typ'] == 'TF') {
				if ($_REQUEST['tf'] == true) {
					$success = Beantwortet::setTrue($schreibt_pruefung_id, $_REQUEST['antwort_id']);
				} else {
					$success = Beantwortet::setFalse($schreibt_pruefung_id, $_REQUEST['antwort_id']);
				}
				
			} else {
				foreach ($antworten as $antwort) {
					if ($_REQUEST['check' .$antwort->getId()]) {
						$success = Beantwortet::setTrue($schreibt_pruefung_id, $antwort->getId());
					} else {
						$success = Beantwortet::setFalse($schreibt_pruefung_id, $antwort->getId());
					}
				}
			}
			
			if ($success) {
				header ("refresh:0; url = /frage/answer/" .$schreibt_pruefung_id .'?next_id=' .key($fragen_map));
			}
			
		}
			
		return new ViewModel([
				'frage'		=> $frage_to_answer,
				'fragen'	=> $fragen,
				'antworten' => $beantwortete,
				'schreibt_pruefung_id' => $schreibt_pruefung_id,
		]);
	}
	private function handleForm($request, $mode) {
		// Array, das mit eventuellen Fehlermeldungen gef�llt wird
		$errors = array();
		
		// Diesen Teil doch in die Methoden?
		$this->frage = new Frage();
		$pruefung = new Pruefung();
		if ($mode == PruefungController::editFragen) {
			$frageid = $_REQUEST["id"];
			
			if (empty($frageid)) {
				$frageid = $this->params()->fromRoute('id');
			}
			$this->frage->load($frageid);
			$pruefungid = $this->frage->getPruefungId();
			
		}elseif ($mode == PruefungController::createFragen) {
			$pruefungid = $_REQUEST["pruefung_id"];
			if (empty($pruefungid)) {
				$pruefungid = $this->params()->fromRoute('id');
			}
		}
			
		if (!$pruefung->load($pruefungid)) {
			array_push($errors, "Fehler beim Laden der Pr�fung!");
		}
		
		$kurs = new Kurs();
		$kurs->load($pruefung->getKursId());
		// Der angemeldete Benutzer muss Admin und/oder Leiter des Kurses sein
		if (!User::currentUser()->istAdmin() && $kurs->getBenutzername() != User::currentUser()->getBenutzername()) {
			array_push($errors, "Sie sind nicht der Leiter dieses Kurses!");
		}
		
		if (strtotime($pruefung->getTermin()) <= time() ) {
			array_push($errors, "Der Pr�fungszeitraum hat bereits begonnen. Die Pr�fung kann nicht mehr bearbeitet werden!");
		}
		
		if (isset($request['speichernFrage'])) {
			// Neues Frage-Objekt mit den Daten aus dem gesendeten Formular erzeugen und in der DB speichern bzw. aktualisieren
			$this->frage = new Frage(
							$request["id"],
							$request["frage_text"],
							$request["punkte"],
							$request["pruefung_id"],
							$request["frage_typ"]);
		
			if (!$this->frage->save()) {
				array_push($errors, "Fehler beim Speichern der Frage. Bitte erneut versuchen!");
				
			}else {
				// Frage konnte gespeichert werden -> Speichern der zugeh�regen Antwort(en)
				switch ($request["frage_typ"]) {
					case "TF":
						$status = 0;
						if ($request["tf"] == "true") {
							$status = 1;
						}
						
						$antwort = new Antwort($request["antwort_id"], "", $this->frage->getId(), $status);
						
						if (!$antwort->save()) array_push($errors, "Fehler beim Speichern der Antwort. Bitte erneut versuchen!");
	
						break;
		
					case "MC":
						$index = 1;
						while (!empty($request["antwort_text" .$index])) {
							$status = 0;
							if ($request["antwort_checked" .$index]) {
								$status = 1;
							}
		
							$antwort = new Antwort(
										$request["antwort_id" .$index],
										$request["antwort_text" .$index],
										$this->frage->getId(),
										$status);
			
							if (!$antwort->save()) array_push($errors, "Fehler beim Speichern der Antwort. Bitte erneut versuchen!");
							
							$index++;
							}
						break;
				}
			}
	
			if (empty($errors)) {
				header ("refresh:0; url = /frage/create/" .$this->frage->getPruefungId());
			}
		}
		
		$viewModel = new ViewModel([
				'pruefung' => $pruefung,
				'fragen'   => Frage::loadList($pruefung->getId()), // Fragen laden -> Was bei Fehler?
				'errors'   => $errors,
				'mode'	   	  => $mode,
				'frageToEdit' => $this->frage
		]);
		
		$viewModel->setTemplate(PruefungController::pathToHtml);
		return $viewModel;
	}
	
	public function createAction() {
		// Berechtigungspr�fung
		if (!User::currentUser()->istAdmin() && !User::currentUser()->istZertifizierer()) {
			header ("refresh:0; url = /user/login/");
		}
		
		return $this->handleForm($_REQUEST, PruefungController::createFragen);
	}
	
	public function editAction() {
		// Berechtigungspr�fung
		if (!User::currentUser()->istAdmin() && !User::currentUser()->istZertifizierer()) {
			header ("refresh:0; url = /user/login/");
		}
	
		return $this->handleForm($_REQUEST, PruefungController::editFragen);
	}

	
	public function deleteAction() {
		// TODO Berechtigungspr�fungen
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
		
		header ("refresh:5; url = /frage/create/" .$frage->getPruefungId());
	}
	
	public function deleteAntwortAction() {
		Antwort::delete($antwort->getId());
		header ("refresh:5; url = /frage/create/" .$this->params()->fromRoute('id'));
	}
	
	/**
	 * Pr�ft, ob eine bestimmte Frage bei einem bestimmten Pr�fungsversuch richtig beantwortet wurde.
	 * 
	 * @param $frage_id Id der Frage, die gepr�ft werden soll.
	 * @param $schreibt_pruefung_id Id des Pr�fungsversuches.
	 * @return boolean true, falls die Frage richtig beantwortet wurde, sonst false.
	 */
	public static function check($frage_id, $schreibt_pruefung_id) {
		// Alle Antwort(en) zur Frage laden
		$antworten = Antwort::loadList($frage_id);
			
		// Zu jeder Antwort den Eintrag in "beantwortet" laden und die abgegebene Antwort �berpr�fen
		foreach ($antworten as $antwort) {
			$beantwortet = new Beantwortet();
			$beantwortet->load($schreibt_pruefung_id, $antwort->getId());
		
			// Wenn mindestens eine Antwort falsch ist, false zur�ckgeben 
			if ($antwort->getStatus() != $beantwortet->getStatus()) {
				return false;
			}
		}
		
		// Es wurde keine falsche Antwort abgegeben
		return true;
			
	}
}