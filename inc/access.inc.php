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

/* 
 MOMENTALNE NEPOUZIVAM 
 - data o prihlaseni prejimam z CMS Sunlight
*/

//Prihlaseni uzivatele 
if ($cms == "logon"){
	$sql = "SELECT 	usr.id,
					login,
					passwd,
					salt,
					logon_count,
					DATE_FORMAT(last_time, '%d. %m. %Y %k:%i:%s') AS last_time,
					last_dns
					FROM users AS usr
					WHERE login = '".$login."' LIMIT 0,1";
	//echo $sql;
	$result = mysql_query($sql);
	//echo $result;
	$user = mysql_fetch_assoc($result);
	//echo $user['login'];
	//echo $user['passwd'];
	//echo $user['salt'];
	//echo $cfg['hash'];
	//echo "<br/>";
	//echo custom_hmac("md5", $cfg['hash'], $user['salt'].$passwd.$user['salt']);
	if($user['passwd'] == custom_hmac("md5", $cfg['hash'], $user['salt'].$passwd.$user['salt'])) {
		//echo "bingo";
		//exit;
		//list(user['id'], user['login'], user['passwd'], user['jmeno'], user['prijmeni']) = $data;
		//$user = $data;
		//echo $user['id'];

		//uzivatele ulozim do session
		$_SESSION['user'] = $user;
		//ulozim, ze je uzivatel prihlasen
		$_SESSION['user']['logged'] = true;
		//zacnu mu pocitat cas prihlaseni
		$_SESSION['user']['access_time'] = time();
		$count = $_SESSION['user']['logon_count']+1;
		
		$sql = "UPDATE `users` 
	 		 	SET `logon_count` = '".$count."', 
			 	  	`last_time` = '".date('Y-m-d  H:i:s')."', 
				  	`last_dns` = '".gethostbyname($_SERVER['HTTP_HOST'])."'   
			 	WHERE `users`.`id` = '".$_SESSION['user']['id']."' 
			 	LIMIT 1";
		$result = mysql_query($sql);
		$_SESSION['user']['logon_count']++;
		//echo "jupiiii";
		
		//pocet radku, ktere generuji
		$count_sql = "SELECT count(*) AS count
			  FROM information_schema.columns
			  WHERE table_name = 'groups'
			  AND table_schema = 'poutnicikolin-cz'";
		
		//vypis nazvu jednotlivych prav
		$column_sql = "SELECT column_name
			   FROM information_schema.columns
			   WHERE table_name = 'groups'
			   AND table_schema = 'poutnicikolin-cz'";
			   
		//pocet radku, ktere generuji
		$count_result = mysql_query($count_sql);
		$rows = mysql_fetch_assoc($count_result);

		//vypis nazvu jednotlivych prav
		$column_result = mysql_query($column_sql);
		
		//zjistim group id
		$gsql = "SELECT `user-group`.group FROM `user-group` WHERE user = '".$_SESSION['user']['id']."' LIMIT 0 , 1";
		$gresult = mysql_query($gsql);
		$group = mysql_fetch_assoc($gresult);
		$gid = $group['group'];

		$i = 0;
		while($column = mysql_fetch_assoc($column_result)) {
			if($i > 2 && $i < ($rows['count'] - 1)) {
				$column = $column['column_name'];
				
				$sql = "SELECT ".$column." FROM groups WHERE id = '".$gid."' LIMIT 1";
				$result = mysql_query($sql);
				$data = mysql_fetch_assoc($result);
		
				$_SESSION['priv'][$column] = $data[$column];
				
			}
			$i++;
		}
	}
	//dotaz neprobehl spravne, spatny login nebo heslo
	else {
		header("Location: ".HTTP_DIR."login.php?error=auth");
		exit;
	}
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
	$sql = "SELECT * FROM `".$cfg['prefix']."-users` WHERE id = '".$_SESSION[SESSION_PREFIX.'user']."'";
	$result = mysql_query($sql);
	if(mysql_num_rows($result)) {
		$user = mysql_fetch_array($result);
		if($_SESSION[SESSION_PREFIX.'password'] != $user['password']) {
			echo "Chybné heslo!";
		}
		else {
			$nologin = false;
			// znovuobnovim pocitani casu
			$_SESSION['user']['access_time'] = time();
		}
		
		$uid = $_SESSION[SESSION_PREFIX.'user'];
		// 20 slunda || 19 dytta || 7 pavlik || 105 liska || 21 Pumpa || 155 jantikjanouch || 165 OVAMysak || 46 Luca || 176 trainmaster || 178 cednik
		if(($uid != 19) && ($uid != 7) && ($uid != 105) && ($uid != 21) && ($uid != 20) && ($uid != 155) && ($uid != 165) && ($uid != 46) && ($uid != 176) && ($uid != 13) && ($uid != 178)){
			header("Location: ".HTTP_DIR."admin/");
		}
	}
	mysql_free_result($result);
}
else {
	session_unset();
	session_destroy();
	header("Location: ".HTTP_DIR."admin/");
	exit('User Is Not Logged');
}
	
?>