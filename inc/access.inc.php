<?php

if(isset($_POST['login'])) $login = $_POST['login'];
else $login = "";
if(isset($_POST['passwd'])) $passwd = $_POST['passwd'];
else $passwd = "";
if(isset($_POST['cms'])) $cms = $_POST['cms'];
elseif(isset($_GET['cms'])) $cms = $_GET['cms'];
else $cms = "";

//odhlaseni uzivatele
if($cms == "logout") {
	//zrusim promenne v session
	session_unset();
	session_destroy();
	//a presmeruji na homepage
	header("Location: ".HTTP_DIR."login.php");
	//ukoncim
	exit;
}

#########################################################################################################

######## Kontrola prihlaseni
$nologin = true;

//kontrola casove existence session a delky jeji necinnosti
if(!isset($_SESSION[SESSION_PREFIX.'user']) || !isset($_SESSION[SESSION_PREFIX.'password'])) {
	$_SESSION['user']["logged"] = false;
	session_unset();
	session_destroy();
	header("Location: ".HTTP_DIR."admin/");
	exit('Session Not Exists');
}
else $_SESSION['user']["logged"] = true;

if(isset($_SESSION['user']['logged']) && ($_SESSION['user']['logged'] == true)) {
	// neverim session z jineho systemu, takze overuju, jestli jsou udaje pravdive
	$user = $database->table($cfg['prefix'] . '-users')->where('id', $_SESSION[SESSION_PREFIX.'user'])->fetch();

	if($user) {
		if($_SESSION[SESSION_PREFIX.'password'] != $user['password']) {
			Tracy\Debugger::log('Access: bad password!', Tracy\Debugger::ERROR);
		}
		else {
			$nologin = false;
			// znovuobnovim pocitani casu
			$_SESSION['user']['access_time'] = time();
		}

		$uid = $_SESSION[SESSION_PREFIX.'user'];
		// 20 slunda || 19 dytta || 7 pavlik || 105 liska || 21 Pumpa || 155 jantikjanouch || 165 OVAMysak || 46 Luca || 2 hvezdar || 178 cednik
		if(($uid != 19) && ($uid != 7) && ($uid != 105) && ($uid != 21) && ($uid != 20) && ($uid != 155) && ($uid != 165) && ($uid != 46) && ($uid != 2) && ($uid != 13) && ($uid != 178)){
			header("Location: ".HTTP_DIR."admin/");
		}
	} else {
		Tracy\Debugger::log('Access: user data does not exist!', Tracy\Debugger::ERROR);
	}
}
else {
	session_unset();
	session_destroy();
	header("Location: ".HTTP_DIR."admin/");
	exit('User Is Not Logged');
}

?>
