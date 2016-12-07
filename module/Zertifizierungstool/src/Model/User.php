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
	
	public function getBenutzername() {
		return $this->benutzername;
	}
	
	public function getVorname() {
		return $this->vorname;
	}
	
	public function getNachname() {
		return $this->nachname;
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
	public function register() {
		$db = new Db_connection();
		
		$this->passwort = $this->saltPasswort($this->passwort);
		
		if (!$this->alreadyExist()){
		$query = "insert into benutzer (benutzername, passwort, vorname, nachname, geburtsdatum, strasse, plz, ort, email, email_bestaetigt, ist_admin, ist_zertifizierer, ist_teilnehmer) values ('"
  				.$this->benutzername."', '".$this->passwort."', '".$this->vorname."', '".$this->nachname."', '"
  				.$this->geburtsdatum."', '".$this->strasse."', '".$this->plz."', '".$this->ort."', '".$this->email."', ".$this->email_bestaetigt.", "
  				.$this->ist_admin.", ".$this->ist_zertifizierer.", ".$this->ist_teilnehmer.");";
		
		
		$result = $db->execute($query);
		$m = $this->registerMail();
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
	 * des Benutzernames dieses Objektes
	 */
	public function registerMail () {
		$empfaenger = $this->email;
		$betreff = "Registrierung Zertifizierungstool";
		$from = "user@zerttool.tk";
		$text = "Sehr geehrte Damen und Herren, bitte bestaetigen Sie folgenden Link: www.zerttool.tk/user/registerbest?benutzer=".$this->benutzername;
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
	
}