<?php
namespace Zertifizierungstool\Model;

use Zertifizierungstool\Model\Db_connection;

/**
 * @author Michael
 *
 */
class User
{
	private $benutzername;
	private $passwort;
	private $vorname;
	private $nachname;
	private $geburtsdatum;
	private $strasse;
	private $plz;
	private $ort;
	private $email;
	private $email_bestaetigt;
	private $ist_admin;
	private $ist_zertifizierer;
	private $ist_teilnehmer;
	
	/** Singleton-Instanz, repr�sentiert den aktuellen Benutzer */
	private static $currentUser;
	
	/**
	 * Liefert den aktuellen Benutzer der Seite zur�ck
	 */
	public static function currentUser() {
		if (!isset(self::$currentUser)) {
			if (isset($_SESSION["currentUser"])) {
				self::$currentUser = unserialize($_SESSION["currentUser"]);
			}else {
 				self::$currentUser = new User();
				
			}
			
		}
		return self::$currentUser;	
	}
	
	/**
	 * Setzt den aktuellen Benutzer
	 */
	public static function setCurrentUser() {
		self::$currentUser = $this;
	}
	
	public function __construct($benutzername, $passwort, $vorname, $nachname, $geburtsdatum, $strasse, $plz, $ort, $email, $email_bestaetigt, $ist_admin, $ist_zertifizierer, $ist_teilnehmer) {
		$this->benutzername     = $benutzername;
		$this->passwort         = $passwort;
		$this->vorname          = $vorname;
		$this->nachname         = $nachname;
		$this->geburtsdatum     = $geburtsdatum;
		$this->strasse          = $strasse;
		$this->plz              = $plz;
		$this->ort              = $ort;
		$this->email            = $email;
		$this->email_bestaetigt = $email_bestaetigt;
		$this->ist_admin         = $ist_admin;
		$this->ist_zertifizierer = $ist_zertifizierer;
		$this->ist_teilnehmer    = $ist_teilnehmer;
	}
	
	public function __construct1(){
		
	}
	/**
	 * L�dt die Daten des Benutzers des �bergebenen Benutzernamens
	 * @param Benutzername des zu ladenden Benutzers
	 * 
	 */
	public function load($benutzername) {
		$db = new Db_connection();
		
		$query = "SELECT * FROM benutzer where benutzername='".$benutzername."';";
		
		$result = $db->execute($query);
		$return_array = array();
		if (mysqli_num_rows($result) > 0) {
			while ($row = mysqli_fetch_assoc($result)) {
				array_push($return_array, $row);
			}
		} else {
			echo "Kein Ergebnis gefunden.";
		}
		
		foreach ($return_array as $row) {	
			$this->benutzername     = $row['benutzername'];
			$this->vorname		    = $row['vorname'];
			$this->nachname		    = $row['nachname'];
			$this->geburtsdatum     = $row['geburtsdatum'];
			$this->strasse          = $row['strasse'];
			$this->plz              = $row['plz'];
			$this->ort              = $row['ort'];
			$this->email            = $row['email'];
			$this->email_bestaetigt  = $row['email_bestaetigt'];
			$this->ist_admin         = $row['ist_admin'];
			$this->ist_zertifizierer = $row['ist_zertifizierer'];
			$this->ist_teilnehmer    = $row['ist_teilnehmer'];
		}
		
		// Fehler pr�fen
	}
	
	/**
	 * L�dt die Daten des Benutzers der �bergebenen E-Mailadresse
	 * @param String E-Mailadresse des zu ladenden Benutzers
	 * @return boolean true falls der Benutzer gefunden wurde
	 * 				   false falls Benutzer mit der �bergebenen Mailadresse nicht gefunden wurde
	 */
	public function load_via_email ($email) {
		$db = new Db_connection();
		
		$query = "SELECT * FROM benutzer where email='".$email."';";
		
		$result = $db->execute($query);
		$return_array = array();
		if (mysqli_num_rows($result) > 0) {
			while ($row = mysqli_fetch_assoc($result)) {
				array_push($return_array, $row);
							}
		
		
			
		foreach ($return_array as $row) {
			$this->benutzername     = $row['benutzername'];
			$this->vorname		    = $row['vorname'];
			$this->nachname		    = $row['nachname'];
			$this->geburtsdatum     = $row['geburtsdatum'];
			$this->strasse          = $row['strasse'];
			$this->plz              = $row['plz'];
			$this->ort              = $row['ort'];
			$this->email            = $row['email'];
			$this->email_bestaetigt  = $row['email_bestaetigt'];
			$this->ist_admin         = $row['ist_admin'];
			$this->ist_zertifizierer = $row['ist_zertifizierer'];
			$this->ist_teilnehmer    = $row['ist_teilnehmer'];
		}
		return true;
	}
		else {
			return false;
		}
		
		
		
	}
	
	public function getBenutzername() {
		return $this->benutzername;
	}
	
	public function getVorname() {
		return $this->vorname;
	}
	
	public function getNachname() {
		return $this->nachname;
	}
	
	public function getGeburtsdatum() {
		return $this->geburtsdatum; 
	}
	
	public function getStrasse() {
		return $this->strasse;
	}
	
	public function getPLZ() {
		return $this->plz;
	}
	
	public function getOrt() {
		return $this->ort;
	}
	
	public function getEmail() {
		return $this->email;
	}
	
	
	public function istAdmin() {
		if ($this->ist_admin==1){
			return true;
		}
		else{
			return false;
		}
	}
	
	public function istZertifizierer() {
		if ($this->ist_zertifizierer==1){
			return true;
		}
		else{
			return false;
		}
	}
	
	public function istTeilnehmer() {
		if ($this->ist_teilnehmer==1){
			return true;
		}
		else{
			return false;
		}
	}
	

	/**
	 * Pr�ft ob ein Benutzer mit dem Nutzernamen dieses Objektes in der Datenbank schon
	 * existiert.
	 * @return Gibt true zur�ck falls er existiert und false falls nicht.
	 */
	public function alreadyExist() {
		$db = new Db_connection();
		
		$query = "Select * from benutzer where benutzername='".$this->benutzername."';";
		$result = $db->execute($query);
		if (mysqli_num_rows($result) == 0) {
			return false;
		}else {
			return true;
		}
	}
	/**
	 * Schreibt einen neuen Datensatz eines Benutzers in die Datenbank. Dabei wird
	 * vorab gepr�ft, ob ein Datensatz mit dem Benutzernamen dieses Objektes schon 
	 * existiert.
	 * @return Bei Erfolg oder nicht Erfolg wird ein entsprechender String zur�ckgeliefert,
	 * der an die View �bergeben werden kann.
	 */
	public function register($invite) {
		$db = new Db_connection();
		
		$this->passwort = $this->saltPasswort($this->passwort);
		
		//Format�nderung des Geburtsdatums in Jahr-Monat-Tag (f�r Datenbank wichtig)
		
		$date = new \DateTime($this->geburtsdatum);
		$this->geburtsdatum=$date->format('Y-m-d');
		
		if (!$this->alreadyExist()){
		$query = "insert into benutzer (benutzername, passwort, vorname, nachname, geburtsdatum, strasse, plz, ort, email, email_bestaetigt, ist_admin, ist_zertifizierer, ist_teilnehmer) values ('"
  				.$this->benutzername."', '".$this->passwort."', '".$this->vorname."', '".$this->nachname."', '"
  				.$this->geburtsdatum."', '".$this->strasse."', '".$this->plz."', '".$this->ort."', '".$this->email."', ".$this->email_bestaetigt.", "
  				.$this->ist_admin.", ".$this->ist_zertifizierer.", ".$this->ist_teilnehmer.");";
		
		
		$result = $db->execute($query);
		
		if ($invite){
			
			$this->registerMailInvite();
			
		}else {
		
			$m = $this->registerMail();
		
		}
		return "Daten wurden erfolgreich gespeichert. Sie erhalten in K&uuml;rze eine E-Mail. Bitte folgen Sie dem darin enthaltenen Link, um Ihre Registrierung abzuschlie&szlig;en.";
	    
	    
		}else {
			
		return "Benutzername ist bereits vergeben! Bitte anderen Namen ausw&auml;hlen!";
		
		}
	}
	/**
	 * Verschl�sselt ein Passwort mit bcrypt Algorithmus
	 * @param Passwort in clearform
	 * @param zus�tzlicher Saltwert, eigentlich unn�tig, da Password_hash selbs salted
	 * @return Verschl�sselter Hashwert des Passworts
	 */
	public function saltPasswort($passwort) {
		return password_hash ($passwort, PASSWORD_DEFAULT);
	}
	/**
	 * �berpr�ft ob der in der DB gespeicherte Hash des Passworts mit dem �bergebenen
	 * Passwort �bereinstimmt. 
	 * @param Eingegebenes Passwort in cleartext
	 * @return Bei �bereinstimmung true ansonsten false
	 */
	public function passwortControll ($passwort) {
		$db = new Db_connection();
		$query = "select passwort from benutzer where benutzername='".$this->benutzername."' and email_bestaetigt = 1;";
		$result = $db->execute($query);
		if (mysqli_num_rows($result) == 0){
			return false;
		}else {
			$row = mysqli_fetch_assoc($result);
			$pruef = password_verify($passwort, $row['passwort']);
			if ($pruef)
				return true;
			else
				return false;
		}
				
	}
	/**
	 * Versendet an die Mailadresse des Objektes eine Mail, zum best�tigen des Accounts
	 * Dazu wird ein Link mit der Route der entsprechenden Action mit zus�tzlichem Parameter
	 * des Benutzernames dieses Objektes erstellt
	 */
	public function registerMail () {
		$empfaenger = $this->email;
		$betreff = "Registrierung Zertifizierungstool";
		$from = "user@zerttool.tk";
		$text = "Hallo ".$this->vorname." ".$this->nachname.",\n\n bitte bestaetigen Sie folgenden Link:\n\n zerttool.tk/user/registerbest?benutzer=".$this->benutzername;
		$text = wordwrap($text, 70);
		mail ($empfaenger, $betreff, $text); 
	}
	
	/**
	 * Versendet an die Mailadresse des Objektes eine Mail, zum best�tigen des Accounts falls zuvor der Teilnehmer eingeladen wurde
	 * Dazu wird ein Link mit der Route der entsprechenden Action mit zus�tzlichem Parameter
	 * des Benutzernames dieses Objektes und der Kurs_id zu der der Teilnehmer eingeladen wurde erstellt
	 */
	public function registerMailInvite() {
		
		$empfaenger = $this->email;
		$betreff = "Registrierung Zertifizierungstool";
		$text = "Hallo ".$this->vorname." ".$this->nachname.",\n\n bitte bestaetigen Sie folgenden Link:\n\n zerttool.tk/user/registerbest?benutzer=".$this->benutzername."&kurs_id=".$_SESSION['kurs'];
		$text = wordwrap($text, 70);
		mail ($empfaenger, $betreff, $text);
		
	}
	
	/**
	 * Versendet an die Mailadresse des Objektes eine Mail, falls durch den Passwort-Vergessen-Link
	 * angefordert. Zus�tzlich wird eine zuf�llige Pr�fzahl generiert, die in der Datenbank gespeichert
	 * wird. Dies soll zus�tzliche Sicherheit gew�hren
	 * Pr�fzahl wird in Link als Parameter �bergeben
	 * 
	 */
	
	public function passwordForgottenMail() {
		
		$db = new Db_connection();
		
		$pruefzahl = mt_rand(0,999999999);
		$query = "update benutzer set pruefzahl =".$pruefzahl." where benutzername='".$this->benutzername."';";
		$result = $db->execute($query);
		
		$empfaenger = $this->email;
		$betreff = "Neues Passwort angefordert f�r Zertifizierungstool";
		$from = "user@zerttool.tk";
		$text = "Hallo ".$this->vorname." ".$this->nachname.",\n\n wenn Sie ein neues Passwort angefordert haben, folgenden Sie bitte diesem Link:\n\n zerttool.tk/user/passwordforgotten?benutzer=".$this->benutzername."&pruefzahl=".$pruefzahl;
		$text = wordwrap($text, 70);
		mail ($empfaenger, $betreff, $text);
				
	}
	/**
	 * Setzt den boolschen Wert email_bestaetigt in der DB von 0 auf 1
	 */
	public function registerbest () {
		$db = new Db_connection();
		$query = "update benutzer set email_bestaetigt=1 where benutzername='".$this->benutzername."';";
		$result = $db->execute($query);
	}
	
	
	/**
	 * �ndert die Werte der Benutzertabelle mit den �bergebenen Werten
	 * @param String $vorname
	 * @param String $nachname
	 * @param String $geburtsdatum
	 * @param String $strasse
	 * @param String $plz
	 * @param String $ort
	 * @param String $email
	 * @return boolean true falls erfolgreich, false falls Datenbankfehler
	 */
	public function update($vorname, $nachname, $geburtsdatum, $strasse, $plz, $ort, $email) {
		$db = new Db_connection();
		
		$date = new \DateTime($geburtsdatum);
		$geburtsdatum=$date->format('Y-m-d');
		$query = "update benutzer set
				vorname='".$vorname."',
				nachname='".$nachname."',
				geburtsdatum='".$geburtsdatum."',
				strasse='".$strasse."',
				plz='".$plz."',
				ort='".$ort."',
				email='".$email."' where benutzername='".$this->benutzername."';";
	
		$result=$db->execute($query);
		return $result;
	}
	
	/**
	 * �ndert das Passwort des Benutzers in der Datenbank. Passwort wird verschl�sselt.
	 * @param String $passwort Neues Passwort
	 * @return boolean true falls erfolgreich, false falls Datenbankfehler
	 */
	public function  updatePassword($passwort) {
		
			$passwort = $this->saltPasswort($passwort);
			$db = new Db_connection();
			$query = "update benutzer set passwort = '".$passwort."', pruefzahl=NULL where benutzername ='".$this->benutzername."';";
			$result = $db->execute($query);
			return $result;

			
	
	}
	
	/**
	 * �berpr�ft �bergebene Pr�fzahl mit Pr�fzahl in Datenbank des Benutzers
	 * @param number $pruefzahl Zu �berpr�fende Pr�fzahl
	 * @return boolean true falls Pr�fzahl �bereinstimmen, false falls nicht
	 */
	public function  check_pruefzahl($pruefzahl) {
	
		$db = new Db_connection();
		$query = "select * from benutzer where benutzername='".$this->benutzername."' and pruefzahl=".$pruefzahl.";";
		$result = $db->execute($query);
		if (mysqli_num_rows($result)<1){
			return false;
		}
		else {
			return true;
		}
	}
	
}