<?php
function printError($error)
{
	// soubor chyb
	$errors = array(
		""					=>	"",
		"ok"				=> "Údaje byly úspěšně nahrány!",
		"email"				=> "Špatně zadaná e-mailová adresa!",
		"error"				=> "Chyba, operace nemohla být provedena!",
		"empty"				=> "Položka musí být vyplněná!",
		"group_num"			=> "Číslo střediska/přístavu nemá požadovaný tvar!",
		"mail_send"			=> "E-mail byl úspěšně odeslán!",
		"already_paid"		=> "Poplatek byl již zaplacen!",
		"del"				=> "Položka byla smazána!",
		"checked"			=> "Položka byla zkontrolována!",
		"unchecked"			=> "Položce byl odebrán příznak!",
	);

	if(!empty($error) && !array_key_exists($error, $errors)) $error = 'ok';

	//pokud nic neprslo, neni co vypisovat
	if(($error == "ok") || ($error == "mail_send")) $html = "<span class='ok'>".$errors[$error]."</span>";
	elseif($error != "") $html = "<span class='error'>".$errors[$error]."</span>";
	else $html = "";

	echo $html;
}
?>
