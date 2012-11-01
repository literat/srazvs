<?php
function printError($error)
	{
	$error_msg = "";
	$ok_msg = "";
	//kdyz prijde prazdna hodnota, resi to switch sam od sebe hodnotou default
	switch($error)
		{
		case "rodne_cislo":		$error_msg = "Špatně zadané rodné číslo!";
			break;
		case "email":			$error_msg = "Špatně zadaná e-mailová adresa!";
			break;
		case "jabber":			$error_msg = "Špatně zadané Jabber ID!";
			break;
		case "live":			$error_msg = "Špatně zadané Live ID!";
			break;
		case "phone":			$error_msg = "Špatně zadané telefoní číslo!";
			break;
		case "gsm":				$error_msg = "Špatně zadané telefoní číslo!";
			break;
		case "zip_code":		$error_msg = "Špatně zadané PSČ!";
			break;
		case "no_login":		$error_msg = "Nezadané přihlašovací jméno!";
			break;
		case "diff_passwd":		$error_msg = "Nová hesla se neschodují!";
			break;
		case "old_passwd":		$error_msg = "Špatně zadané původní heslo!";
			break;
		case "unique_login":	$error_msg = "Zadané jméno již existuje!";
			break;
		case "no_old_passwd":	$error_msg = "Nezadali jste původní heslo!";
			break;
		case "no_new_passwd":	$error_msg = "Nezadali jste nové heslo!";
			break;
		case "no_new2_passwd":	$error_msg = "Nezadali jste zvonu nové heslo!";
			break;
		case "no_passwd":		$error_msg = "Nezadali jste žádné heslo!";
			break;
		case "bad_profile_img":	$error_msg = "Moc velký obrázek! Rozměry musí být menší než 200 x 200 a velikost nesmí přesáhnout 30 kB!";
			break;
		case "error":			$error_msg = "Chyba, operace nemohla být provedena!";
			break;
		case "ok":				$ok_msg = "Údaje byly úspěšně nahrány!";
			break;
		default: $error_msg = "";
		}
	//pokud nic neprslo, neni co vypisovat
	if($error_msg !== "") $html = "<span class='error'>".$error_msg."</span>";
	elseif($ok_msg !=="") $html = "<span class='ok'>".$ok_msg."</span>";
	else $html = "";
	echo $html;
	}
?>