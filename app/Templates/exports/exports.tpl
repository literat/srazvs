<div class='siteContentRibbon'>Exporty</div>
<div style="width:22%;float:left;">
	<div class='pageRibbon'>Tisk</div>
		<div>
			<a style='text-decoration:none; display:block; margin-bottom:4px;' href='export/evidence?type=confirm'>
				<img style='border:none;' align='absbottom' src='<?php echo IMG_DIR; ?>icons/pdf.png' />
				potvrzení o přijetí zálohy
			</a>

			<a style='text-decoration:none; display:block; margin-bottom:4px;' href='export/evidence?type=evidence'>
				<img style='border:none;' align='absbottom' src='<?php echo IMG_DIR; ?>icons/pdf.png' />
				příjmový pokladní doklad
			</a>

			<a style='text-decoration:none; display:block; margin-bottom:4px;' href='export/program-public?cms=public'>
				<img style='border:none;' align='absbottom' src='<?php echo IMG_DIR; ?>icons/pdf.png' />
				veřejný program
			</a>

			<a style='text-decoration:none; display:block; margin-bottom:4px;' href='export/program-cards'>
				<img style='border:none;' align='absbottom' src='<?php echo IMG_DIR; ?>icons/pdf.png' />
				osobní program
			</a>

			<a style='text-decoration:none; display:block; margin-bottom:4px;' href='export/program-large'>
				<img style='border:none;' align='absbottom' src='<?php echo IMG_DIR; ?>icons/pdf.png' />
				program srazu - velký formát
			</a>

			<a style='text-decoration:none; display:block; margin-bottom:4px;' href='export/program-badge'>
				<img style='border:none;' align='absbottom' src='<?php echo IMG_DIR; ?>icons/pdf.png' />
				program srazu - do visačky
			</a>

			<a style='text-decoration:none; display:block; margin-bottom:4px;' href='export/name-badges'>
				<img style='border:none;' align='absbottom' src='<?php echo IMG_DIR; ?>icons/pdf.png' />
				jmenovky
			</a>

			<a style='text-decoration:none; display:block; margin-bottom:4px;' href='export/attendance'>
				<img style='border:none;' align='absbottom' src='<?php echo IMG_DIR; ?>icons/pdf.png' />
				prezenční listina
			</a>

			<a style='text-decoration:none; display:block; margin-bottom:4px;' href='export/name-list'>
				<img style='border:none;' align='absbottom' src='<?php echo IMG_DIR; ?>icons/pdf.png' />
				jmenný seznam
			</a>

			<a style='text-decoration:none; display:block; margin-bottom:4px;' href='export/meal-ticket'>
				<img style='border:none;' align='absbottom' src='<?php echo IMG_DIR; ?>icons/pdf.png' />
				stravenky
			</a>
			<!--
			<a style='text-decoration:none; display:block; margin-bottom:4px;' href='feedback.pdf.php'>
				<img style='border:none;' align='absbottom' src='<?php echo IMG_DIR; ?>icons/pdf.png' />
				zpětná vazba
			</a>
			-->
			<a style='text-decoration:none; display:block; margin-bottom:4px;' href='export/evidence?type=summary'>
				<img style='border:none;' align='absbottom' src='<?php echo IMG_DIR; ?>icons/pdf.png' />
				kompletní příjmový pokladní doklad
			</a>

			<a style='text-decoration:none; display:block; padding-right:4px;' href='export/visitor-excel'>
				<img style='border:none;' align='absbottom' src='<?php echo IMG_DIR; ?>icons/xlsx.png' />
				data účastníků
			</a>

			<a style='text-decoration:none; display:block; margin-bottom:4px; margin-top:4px;' href='export/program-details'>
				<img style='border:none;' align='absbottom' src='<?php echo IMG_DIR; ?>icons/pdf.png' />
				detaily programů
			</a>

		</div>
	</div>


	<div style="width:44%;padding-left:0.5%;float:right;">
		<div class='pageRibbon'>Graf přihlašování</div>
		<?php echo $data['graph']; ?>
	</div>

	<div style="width:15%;padding-left:0.5%;float:right;">
		<div class='pageRibbon'>Jídlo</div>
		<?php echo $data['meals']; ?>
	</div>

	<div style="padding-left:22.5%;padding-right:60%;margin-top:6px;height:<?php echo $data['graphHeight']; ?>px;">
		<div class='pageRibbon'>Peníze</div>
		<div style="margin-bottom:4px;">Celkem vybráno: <strong><?php echo $data['account']; ?></strong>,-Kč</div>
		<div style="margin-bottom:4px;">Zbývá vybrat: <strong><?php echo $data['balance']; ?></strong>,-Kč</div>
		<div style="margin-bottom:4px;">Suma srazu celkem: <strong><?php echo $data['suma']; ?></strong>,-Kč</div>
	</div>

	<div style="width:50%;float:left;">
		<div class='pageRibbon'>Programy</div>
		<?php echo $data['programs']; ?>
	</div>

	<div style="width:49.5%;padding-left:50.5%;">
		<div class='pageRibbon'>Materiál</div>
		<div>
			<?php echo $data['materials']; ?>
		</div>
	</div>
	<div class="cleaner"></div>
	<div>
		<div class='pageRibbon'><span>Formulář na jmenovky</span></div>
		<div>
			<form action='?name-badges' method='post'>
				<label>Jména (slova oddělená čárkou):</label>
				<textarea style="max-width:100%;" name="names" cols="120" rows="5"></textarea>
				<div class='button-line'>
					<button type='submit' onclick=\"this.form.submit()\">
						<img src='<?php echo IMG_DIR; ?>icons/save.png' /> Exportovat</button>
				</div>
				<input type="hidden" name="cms" value="name-badges" />
			</form>
		</div>
	</div>

<!--<div class='pageRibbon'>Něco dalšího</div>-->
