<?php
//header
require_once('../inc/define.inc.php');

######################### PRISTUPOVA PRAVA ################################

include_once(INC_DIR.'access.inc.php');

########################### POST a GET #########################

$id = requested("id","");
$cms = requested("cms","");
$search = requested("search","");
$error = requested("error","");

$mail = requested("mail","");
$test_mail = requested("test-mail","");
$subject = requested("subject","");
$message = requested("message","");

if($cms == 'update'){
	$json_encoded = array('subject' => $subject, 'message' => $message);
	$json_encoded = json_encode($json_encoded);
	$json_encoded = mysql_real_escape_string($json_encoded);
	
	$update_query = 'UPDATE kk_settings
					 SET value = \''.$json_encoded.'\'
					 WHERE name = \'mail_'.$mail.'\'';
	$update_result = mysql_query($update_query);
	
	if($update_query){
		$error = 'E_UPDATE_NOTICE';
		$error = 'ok';
	}
	else {
		$error = 'E_UPDATE_ERROR';
	}
}

if($cms == 'sendmail'){	
	$mail_query = "SELECT * FROM kk_settings WHERE name = 'mail_".$mail."'";
	$mail_result = mysql_query($mail_query);
	$mail_data = mysql_fetch_assoc($mail_result);

	$mail_value = json_decode($mail_data['value']);
	
	$Container = new Container($GLOBALS['cfg']);
	$Emailer = $Container->createEmailer();
	if($Emailer->sendMail($test_mail, $test_mail, html_entity_decode($mail_value->subject), html_entity_decode($mail_value->message))) {
		redirect("index.php?error=mail_send");
	}
	
	if($return){
		$error = 'E_MAIL_NOTICE';
		$error = 'mail_send';
	}
	else {
		$error = 'E_MAIL_ERROR';
	}
}

################## GENEROVANI STRANKY #############################

include_once(INC_DIR.'http_header.inc.php');
include_once(INC_DIR.'header.inc.php');

?>

<div class='siteContentRibbon'>Nastavení systému</div>
<?php printError($error); ?>
<div class='pageRibbon'>E-maily</div>
<?php
$setSql = "SELECT * FROM kk_settings WHERE name = 'mail_pay'";
$setResult = mysql_query($setSql);
$setData = mysql_fetch_assoc($setResult);

$json = json_decode($setData['value']);
?>
<div class='pageContentRibbon'>Zaplacení poplatku</div>
<div>
	<div style="float:right; width:38%;padding-top:30px;">
		<strong>Náhled:</strong>
		<br />
		<br />
		<div style="height:135px;">
			<?php echo html_entity_decode($json->message); ?>
		</div>
		<form action='index.php' method='post'>
			<label>E-mail:</label><br />
			<input type="text" name="test-mail" size="50" /><br />
			<input type="hidden" name="cms" value="sendmail" />
			<input type="hidden" name="mail" value="pay" />
			<button type='button' onclick="this.form.submit()">
				<img src='<?php echo $ICODIR; ?>small/mail.png'  /> Odeslat náhled</button>
		</form>
	</div>

	<form action='index.php' method='post'>
		<label>Předmět:</label><br />
		<input type="text" name="subject" value="<?php echo $json->subject; ?>" size="50" /><br /><br />
		<label>Obsah:</label>
		<textarea style="max-width:60%;" name="message" cols="100" rows="10"><?php echo $json->message; ?></textarea>
		<input type="hidden" name="cms" value="update" />
		<input type="hidden" name="mail" value="pay" />
		<div class='button-line'>
			<button type='submit' onclick=\"this.form.submit()\">
				<img src='<?php echo $ICODIR; ?>small/save.png' /> Uložit</button>
		</div>
	</form>
</div>

<div class="cleaner">&nbsp;</div>

<div class='pageContentRibbon'>Zaplacení zálohy</div>
<div>
	<div style="float:right; width:38%;padding-top:30px;">
		<strong>Náhled:</strong>
		<br />
		<br />
		<div style="height:135px;">
<?php
$setSql = "SELECT * FROM kk_settings WHERE name = 'mail_advance'";
$setResult = mysql_query($setSql);
$setData = mysql_fetch_assoc($setResult);

$json = json_decode($setData['value']);

echo html_entity_decode($json->message);
?>
		</div>
		<form action='index.php' method='post'>
			<label>E-mail:</label><br />
			<input type="text" name="test-mail" size="50" /><br />
			<input type="hidden" name="cms" value="sendmail" />
			<input type="hidden" name="mail" value="advance" />
			<button type='button' onclick="this.form.submit()">
				<img src='<?php echo IMG_DIR; ?>icons/mail.png'  /> Odeslat náhled</button>
		</form>
	</div>

	<form action='index.php' method='post'>
		<label>Předmět:</label><br />
		<input type="text" name="subject" value="<?php echo $json->subject; ?>" size="50" /><br /><br />
		<label>Obsah:</label>
		<textarea style="max-width:60%;" name="message" cols="100" rows="10"><?php echo $json->message; ?></textarea>
		<input type="hidden" name="cms" value="update" />
		<input type="hidden" name="mail" value="advance" />
		<div class='button-line'>
			<button type='submit' onclick=\"this.form.submit()\">
				<img src='<?php echo IMG_DIR; ?>icons/save.png' /> Uložit</button>
		</div>
	</form>
</div>

<div class="cleaner">&nbsp;</div>

<div class='pageContentRibbon'>Zpráva lektorovi</div>
<div>
	<div style="float:right; width:38%;padding-top:30px;">
		<strong>Náhled:</strong>
		<br />
		<br />
		<div>
<?php
$setSql = "SELECT * FROM kk_settings WHERE name = 'mail_tutor'";
$setResult = mysql_query($setSql);
$setData = mysql_fetch_assoc($setResult);

$json = json_decode($setData['value']);

echo html_entity_decode($json->message);
?>
		</div>
		<form action='index.php' method='post'>
			<label>E-mail:</label><br />
			<input type="text" name="test-mail" size="50" /><br />
			<input type="hidden" name="cms" value="sendmail" />
			<input type="hidden" name="mail" value="tutor" />
			<button type='button' onclick="this.form.submit()">
				<img src='<?php echo IMG_DIR; ?>icons/mail.png'  /> Odeslat náhled</button>
		</form>
	</div>
	<form action='index.php' method='post'>
		<label>Předmět:</label><br />
		<input type="text" name="subject" value="<?php echo $json->subject; ?>" size="50" /><br /><br />
		<label>Obsah:</label>
		<textarea style="max-width:60%;" name="message" cols="100" rows="20"><?php echo $json->message; ?></textarea>
		<input type="hidden" name="cms" value="update" />
		<input type="hidden" name="mail" value="tutor" />
		<div class='button-line'>
			<button type='submit' onclick=\"this.form.submit()\">
				<img src='<?php echo IMG_DIR; ?>icons/save.png' /> Uložit</button>
		</div>
	</form>
</div>

<div class="cleaner">&nbsp;</div>

<div class='pageContentRibbon'>Zpráva po registraci</div>
<div>
	<div style="float:right; width:38%;padding-top:30px;">
		<strong>Náhled:</strong>
		<br />
		<br />
		<div>
<?php
$setSql = "SELECT * FROM kk_settings WHERE name = 'mail_post_reg'";
$setResult = mysql_query($setSql);
$setData = mysql_fetch_assoc($setResult);

$json = json_decode($setData['value']);

echo html_entity_decode($json->message);
?>
		</div>
		<form action='index.php' method='post'>
			<label>E-mail:</label><br />
			<input type="text" name="test-mail" size="50" /><br />
			<input type="hidden" name="cms" value="sendmail" />
			<input type="hidden" name="mail" value="post_reg" />
			<button type='button' onclick="this.form.submit()">
				<img src='<?php echo IMG_DIR; ?>icons/mail.png'  /> Odeslat náhled</button>
		</form>
	</div>
	<form action='index.php' method='post'>
		<label>Předmět:</label><br />
		<input type="text" name="subject" value="<?php echo $json->subject; ?>" size="50" /><br /><br />
		<label>Obsah:</label>
		<textarea style="max-width:60%;" name="message" cols="100" rows="20"><?php echo $json->message; ?></textarea>
		<input type="hidden" name="cms" value="update" />
		<input type="hidden" name="mail" value="post_reg" />
		<div class='button-line'>
			<button type='submit' onclick=\"this.form.submit()\">
				<img src='<?php echo IMG_DIR; ?>icons/save.png' /> Uložit</button>
		</div>
	</form>
</div>

<div class="cleaner">&nbsp;</div>

<?php

###################################################################

include_once(INC_DIR.'footer.inc.php');

###################################################################
?>