<?php
function printError($error)
{	
	// soubor chyb
	$errors = array(
		""					=>	"",
		"ok"				=> "Údaje byly úspěšně nahrány!",
		"rodne_cislo"		=> "Špatně zadané rodné číslo!",
		"email"				=> "Špatně zadaná e-mailová adresa!",		
		"jabber"			=> "Špatně zadané Jabber ID!",
		"live"				=> "Špatně zadané Live ID!",
		"phone"				=> "Špatně zadané telefoní číslo!",
		"gsm"				=> "Špatně zadané telefoní číslo!",
		"zip_code"			=> "Špatně zadané PSČ!",
		"no_login"			=> "Nezadané přihlašovací jméno!",
		"diff_passwd"		=> "Nová hesla se neschodují!",
		"old_passwd"		=> "Špatně zadané původní heslo!",
		"unique_login"		=> "Zadané jméno již existuje!",
		"no_old_passwd"		=> "Nezadali jste původní heslo!",
		"no_new_passwd"		=> "Nezadali jste nové heslo!",
		"no_new2_passwd"	=> "Nezadali jste zvonu nové heslo!",
		"no_passwd"			=> "Nezadali jste žádné heslo!",
		"bad_profile_img"	=> "Moc velký obrázek! Rozměry musí být menší než 200 x 200 a velikost nesmí přesáhnout 30 kB!",
		"error"				=> "Chyba, operace nemohla být provedena!",
		"empty"				=> "Položka musí být vyplněná!",
		"group_num"			=> "Číslo střediska/přístavu nemá požadovaný tvar!",
		"mail_send"			=> "E-mail byl úspěšně odeslán!",
		"already_paid"		=> "Poplatek byl již zaplacen!",
		"del"				=> "Položka byla smazána!"
	);
	
	//pokud nic neprslo, neni co vypisovat
	if(($error == "ok") || ($error == "mail_send")) $html = "<span class='ok'>".$errors[$error]."</span>";
	elseif($error != "") $html = "<span class='error'>".$errors[$error]."</span>";
	else $html = "";
	
	echo $html;
}
?>