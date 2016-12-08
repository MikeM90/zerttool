<?php
namespace Zertifizierungstool\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zertifizierungstool\Model\User;

/**
 * Dokumentation
 * 
 * @author
 *
 */
class UserController extends AbstractActionController
{
	public function registerAction()
	{
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			
		
		
		$user= new User($_REQUEST["benutzername"], $_REQUEST["passwort"], $_REQUEST["vorname"], $_REQUEST["nachname"], $_REQUEST["geburtsdatum"], $_REQUEST["strasse"], $_REQUEST["plz"], $_REQUEST["ort"], $_REQUEST["email"], $_REQUEST["email_bestaetigt"], $_REQUEST["ist_admin"], $_REQUEST["ist_zertifizierer"], $_REQUEST["ist_teilnehmer"]);
		
		$result = $user->register();
		
		
		return new ViewModel(['meldung' => $result]);
		}
		
		else
			return new ViewModel();
	}
	
	public function registertestAction()
	{
		$user = new User("michi", "123", "Michael", "Moertl", "1990-11-26", "Nibelungenstrasse","94032", "passau", "moertl05@gw.uni-passau.de", 0, 1, 0, 0);
	
		$user->register();

		return new ViewModel();
	}
	public function anmeldetestAction()
	{
		$user = new User();
		$user->load("michi");
		echo $user->getBenutzername();
		echo $user->saltPasswort("123", $user->getBenutzername());
		$result=$user->passwortControll("123");
		if ($result){
			echo "Erfolgreich";

		}
		else {
			echo "Fehlgeschlagen";
		}
	}
	public function registerbestAction() {
		$user = new User();
		$user->load($_GET['benutzer']);
		$user->registerbest();
		return new ViewModel();
	}
	
	public function loginAction()
	{
		
		
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
			
			// Vielleicht zuerst die Login-Daten pr�fen bevor man alle Daten des Benutzers l�dt?
			// Also im Model ne Methode login($benutzername, $passwort), die zuerst die Daten
			// pr�ft und dann alle Felder bef�llt oder load() aufruft
			User::currentUser()->load($_POST['benutzername']);
			
			$result = User::currentUser()->passwortControll($_POST['passwort']);
			if ($result){
				$_SESSION["currentUser"] = serialize(User::currentUser());
				return new ViewModel(['anmeldestatus' => true]);
			}
			else {
				return new ViewModel(['anmeldestatus' => false]);
			}
		}
		
		
		return new ViewModel();

	}
	
	
	/*public function logoutAction() {
		
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000, $params["path"],
					$params["domain"], $params["secure"], $params["httponly"]
					);
		}
		
		session_destroy();
	}*/
	
	
	
	public function loeschenAction() {
		
	}
}