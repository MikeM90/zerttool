<?php


/*
 *	Zeigt vom Benutzer angelegte/besuchte Kurse
*/
//Sicht vom Leiter

if(User::currentUser()->getBenutzername()==NULL){
	header("refresh:0; url= /user/login");
	exit;
}

else{
	User::currentUser()->load(User::currentUser()->getBenutzername());
	$_SESSION["currentUser"] = serialize(User::currentUser());
	
	$db = new Db_connection();
	
	$query = "SELECT * FROM kurs WHERE benutzername = ".$_SESSION["currentUser"].";";
	
	$kurse = $db->execute($query);
	$return_array = array();
	if (mysqli_num_rows($kurse) > 0) {
		while ($row = mysqli_fetch_assoc($kurse)) {
			array_push($return_array, $row);
		}
	} else {
		echo "Keine Kurse vorhanden.";
	}
	
	foreach ($return_array as $row) {
		$this->kurs_name 	= 	$row['kurs_name'];
		$this->kurs_start 	= 	$row['kurs_start'];
		$this->kurs_ende  	= 	$row['kurs_ende'];
		//$this->sichbarkeit 	=	$row['sichtbarkeit'];
	
	}
	
}

?>

<h1>Meine Kurse</h1>