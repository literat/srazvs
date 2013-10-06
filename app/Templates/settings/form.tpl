<div class='siteContentRibbon'>Nastavení systému</div>
<?php echo $data['error']; ?>
<div class='pageRibbon'>E-maily</div>
<div class='pageContentRibbon'>Zaplacení poplatku</div>
<div>
	<div style="float:right; width:38%;padding-top:30px;">
		<strong>Náhled:</strong>
		<br />
		<br />
		<div style="height:135px;">
			<?php echo $data['payment_html_message']; ?>
		</div>
		<form action='?settings' method='post'>
			<label>E-mail:</label><br />
			<input type="text" name="test-mail" size="50" /><br />
			<input type="hidden" name="cms" value="mail" />
			<input type="hidden" name="mail" value="cost" />
			<button type='button' onclick="this.form.submit()">
				<img src='<?php echo IMG_DIR; ?>icons/mail.png'  /> Odeslat náhled</button>
		</form>
	</div>

	<form action='?settings' method='post'>
		<label>Předmět:</label><br />
		<input type="text" name="subject" value="<?php echo $data['payment_subject']; ?>" size="50" /><br /><br />
		<label>Obsah:</label>
		<textarea style="max-width:60%;" name="message" cols="100" rows="10"><?php echo $data['payment_message']; ?></textarea>
		<input type="hidden" name="cms" value="update" />
		<input type="hidden" name="mail" value="cost" />
		<div class='button-line'>
			<button type='submit' onclick=\"this.form.submit()\">
				<img src='<?php echo IMG_DIR; ?>icons/save.png' /> Uložit</button>
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
			<?php echo $data['advance_html_message']; ?>
		</div>
		<form action='?settings' method='post'>
			<label>E-mail:</label><br />
			<input type="text" name="test-mail" size="50" /><br />
			<input type="hidden" name="cms" value="mail" />
			<input type="hidden" name="mail" value="advance" />
			<button type='button' onclick="this.form.submit()">
				<img src='<?php echo IMG_DIR; ?>icons/mail.png'  /> Odeslat náhled</button>
		</form>
	</div>

	<form action='?settings' method='post'>
		<label>Předmět:</label><br />
		<input type="text" name="subject" value="<?php echo $data['advance_subject']; ?>" size="50" /><br /><br />
		<label>Obsah:</label>
		<textarea style="max-width:60%;" name="message" cols="100" rows="10"><?php echo $data['advance_message']; ?></textarea>
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
			<?php echo $data['tutor_html_message']; ?>
		</div>
		<form action='?settings' method='post'>
			<label>E-mail:</label><br />
			<input type="text" name="test-mail" size="50" /><br />
			<input type="hidden" name="cms" value="mail" />
			<input type="hidden" name="mail" value="tutor" />
			<button type='button' onclick="this.form.submit()">
				<img src='<?php echo IMG_DIR; ?>icons/mail.png'  /> Odeslat náhled</button>
		</form>
	</div>
	<form action='?settings' method='post'>
		<label>Předmět:</label><br />
		<input type="text" name="subject" value="<?php echo $data['tutor_subject']; ?>" size="50" /><br /><br />
		<label>Obsah:</label>
		<textarea style="max-width:60%;" name="message" cols="100" rows="20"><?php echo $data['tutor_message']; ?></textarea>
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
			<?php echo $data['reg_html_message']; ?>
		</div>
		<form action='?settings' method='post'>
			<label>E-mail:</label><br />
			<input type="text" name="test-mail" size="50" /><br />
			<input type="hidden" name="cms" value="mail" />
			<input type="hidden" name="mail" value="post_reg" />
			<button type='button' onclick="this.form.submit()">
				<img src='<?php echo IMG_DIR; ?>icons/mail.png'  /> Odeslat náhled</button>
		</form>
	</div>
	<form action='?settings' method='post'>
		<label>Předmět:</label><br />
		<input type="text" name="subject" value="<?php echo $data['reg_subject']; ?>" size="50" /><br /><br />
		<label>Obsah:</label>
		<textarea style="max-width:60%;" name="message" cols="100" rows="20"><?php echo $data['reg_message']; ?></textarea>
		<input type="hidden" name="cms" value="update" />
		<input type="hidden" name="mail" value="post_reg" />
		<div class='button-line'>
			<button type='submit' onclick=\"this.form.submit()\">
				<img src='<?php echo IMG_DIR; ?>icons/save.png' /> Uložit</button>
		</div>
	</form>
</div>

<div class="cleaner">&nbsp;</div>