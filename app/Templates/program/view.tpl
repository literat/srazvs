<?php include_once(TPL_DIR."vodni_header.tpl"); ?>

	<!-- content -->
	<div id="content-program">
	<div id="content-pad-program">
	<h1>Program srazu vodních skautů</h1>
	<br />
	<h2><?php echo $data['meeting_heading']; ?></h2>
	<br />
	<p>info: Po rozkliknutí programu se Vám zobrazí jeho detail.</p>

	<!-- PROGRAM SRAZU -->

<?php if($data['display_program']){ ?>

	<?php echo $data['public_program']; ?>

	<br />
	<!--<a style="text-decoration:none; padding-right:4px;" href="program.pdf.php">
    	<img style='border:none;' align='absbottom' src='<?php echo IMG_DIR; ?>icons/pdf.png' />
    </a>
	<a href="program.pdf.php">Stáhněte si program srazu ve formátu PDF</a>
    <br />
    <a style="text-decoration:none; padding-right:4px;" href="exports/?program-details">
    	<img style='border:none;' align='absbottom' src='<?php echo IMG_DIR; ?>icons/pdf.png' />
    </a>
	<a href="exports/?program-details">Stáhněte si detaily programů srazu ve formátu PDF</a>-->
	<p style="text-align:center; font-size:medium;">Změna programu vyhrazena!</p>

	<!-- PROGRAM SRAZU -->

<?php } else { ?>
		<div>Registrace není otevřena, sraz se stále ještě připravuje!</div>
<?php } ?>
				<p></p>
				<p style="text-align: center; "></p>
			</div>
		</div>
		<div class="cleaner"></div>
	</div>
</div>

<?php include_once(TPL_DIR."vodni_footer.tpl"); ?>