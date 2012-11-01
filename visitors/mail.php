<?php
//header
require_once('../inc/define.inc.php');

######################### PRISTUPOVA PRAVA ################################

include_once($INCDIR.'access.inc.php');

################################ SQL, KONTROLA ############################

$id = requested("id","");
$cms = requested("cms","");
$to = requested("to","");
$bcc = requested("bcc","");
$subject = requested("subject","");
$message = requested("message","");
$error = requested("error","");

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

$to = preg_replace("/\n/","",$to);

// To send HTML mail, the Content-type header must be set
$headers = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
		
// Additional headers
$headers .= "To: Sraz VS <srazvs@vodni.skauting.cz>\r\n";
$headers .= "From: Sraz VS <srazvs@vodni.skauting.cz>\r\n";
$headers .= "Bcc: ".$to."\r\n";

if($cms == 'send'){
	$message = $cfg['mail-html-header']."<body>\n".$message."\n</body>\n</html>";
	$return = mail("srazvs@vodni.skauting.cz", $subject, $message, $headers);
	
	if($return){
		$error = 'E_MAIL_NOTICE';
		$error = 'mail_send';
		redirect("index.php?error=".$error);
	}
	else {
		$error = 'E_MAIL_ERROR';
	}
}

$emails = "";
$bcc = "";
$query = "SELECT email FROM kk_visitors WHERE id IN (".$query_id.") GROUP BY email";
$query_result = mysql_query($query);
while($data = mysql_fetch_assoc($query_result)){
	$emails .= $data['email'].",\n";
	$bcc .= "<".$data['email'].">,";
}

$emails = rtrim($emails, "\n,");
$bcc = rtrim($bcc, ",");

################## GENEROVANI STRANKY #############################

include_once($INCDIR.'http_header.inc.php');
include_once($INCDIR.'header.inc.php');

?>

<div class='siteContentRibbon'>Správa účastníků</div>
<?php printError($error); ?>
<div class='pageRibbon'>hromadný e-mail</div>
<div>

<form action='mail.php' method='post'>
	<label>Příjemci:</label><br />
	<textarea name="to" cols="40" rows="30"><?php echo $emails; ?></textarea><br /><br />
	<label>Předmět:</label><br />
	<input type="text" name="subject" size="100" /><br /><br />
	<label>Obsah:</label><br />
	<textarea style="max-width:60%;" name="message" cols="150" rows="20"></textarea>
	<input type="hidden" name="cms" value="send" />
	<input type="hidden" name="bcc" value="<?php echo $bcc; ?>" />
	<div class='button-line'>
		<button type='submit' onclick=\"this.form.submit()\">
			<img src='<?php echo $ICODIR; ?>small/mail.png' /> Odeslat</button>
	</div>
</form>

<?php

###################################################################

include_once($INCDIR.'footer.inc.php');

###################################################################
?>