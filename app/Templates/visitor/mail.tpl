<div class='siteContentRibbon'>Správa účastníků</div>
<?php echo $data['error']; ?>
<div class='pageRibbon'>hromadný e-mail</div>
<div>

<form action='?visitor' method='post'>
	<div class='button-line'>
		<button type='submit' onclick=\"this.form.submit()\">
			<img src='<?php echo IMG_DIR; ?>icons/mail.png' /> Odeslat</button>
	</div>
	<label>Příjemci:</label><br />
	<textarea name="recipients" cols="40" rows="30"><?php echo $data['recipient_mails']; ?></textarea><br /><br />
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