<?php
//header
require_once('../inc/define.inc.php');

######################### PRISTUPOVA PRAVA ################################

include_once(INC_DIR.'access.inc.php');

################################ SQL, KONTROLA ############################

$id = requested("id","");
$cms = requested("cms","");
$recipients = requested("recipients","");
$subject = requested("subject","");
$message = requested("message","");
$error = requested("error","");

$Container = new Container($GLOBALS['cfg'], NULL);
$Emailer = $Container->createEmailer();

if(isset($_POST['checker'])){
	$id = $_POST['checker'];
	$query_id = NULL;
	foreach($id as $key => $value) {
		$query_id .= $value.',';
	}
	$query_id = rtrim($query_id, ',');
}
else {
	$query_id = $id;	
}

$bcc_mail = preg_replace("/\n/","",$recipients);

$recipient_name = $cfg['mail-sender-name'];
$recipient_mail = $cfg['mail-sender-address'];

// space to &nbsp;
$message = str_replace(" ","&nbsp;",$message);
// new line to <br> and tags stripping
$message = nl2br(strip_tags($message));

if($cms == 'send'){
	$message = "<html><head><title>".$subject."</title></head><body>\n".$message."\n</body>\n</html>";
	
	$return = $Emailer->sendMail($recipient_mail, $recipient_name, $subject, $message, $bcc_mail);
	
	if($return){
		$error = 'E_MAIL_NOTICE';
		$error = 'mail_send';
		redirect("index.php?error=".$error);
	}
	else {
		$error = 'E_MAIL_ERROR';
	}
}

$recipient_mails = "";
$query = "SELECT email FROM kk_visitors WHERE id IN (".$query_id.") GROUP BY email";
$query_result = mysql_query($query);
while($data = mysql_fetch_assoc($query_result)){
	$recipient_mails .= $data['email'].",\n";
}
$recipient_mails = rtrim($recipient_mails, "\n,");

################## GENEROVANI STRANKY #############################

include_once(INC_DIR.'http_header.inc.php');
include_once(INC_DIR.'header.inc.php');

?>

<div class='siteContentRibbon'>Správa účastníků</div>
<?php printError($error); ?>
<div class='pageRibbon'>hromadný e-mail</div>
<div>

<form action='mail.php' method='post'>
	<label>Příjemci:</label><br />
	<textarea name="recipients" cols="40" rows="30"><?php echo $recipient_mails; ?></textarea><br /><br />
	<label>Předmět:</label><br />
	<input type="text" name="subject" size="100" /><br /><br />
	<label>Obsah:</label><br />
	<textarea style="max-width:60%;" name="message" cols="150" rows="20"></textarea>
	<input type="hidden" name="cms" value="send" />
	<div class='button-line'>
		<button type='submit' onclick=\"this.form.submit()\">
			<img src='<?php echo IMG_DIR; ?>icons/mail.png' /> Odeslat</button>
	</div>
</form>

<?php

###################################################################

include_once(INC_DIR.'footer.inc.php');

###################################################################
?>