<!-- start of body -->
	<body>
	<!-- start of canvas -->
	<div id='canvas'>
		<!-- start of headerCanvas-->
		<div id='headerCanvas'>
			<div id="headerContent">
				<div id="headerProfileLogout">
					<a href="<?php echo HTTP_DIR."srazvs/"; ?>index.php" title="domů"><span>Domů</span></a>&nbsp;|&nbsp;
					<a href="<?php echo SET_DIR; ?>/" title="nastavení"><span>Nastavení</span></a>&nbsp;|&nbsp;
					<a href="<?php echo HTTP_DIR."admin/"; ?>" title="administrace"><span>Administrace</span></a>&nbsp;|&nbsp;
					<a href="<?php echo HTTP_DIR."srazvs/registration/"; ?>" title="registrace" target="_blank"><span>Registrace</span></a>&nbsp;|&nbsp;
					<a href="<?php echo HTTP_DIR."srazvs/program/?cms=public"; ?>" title="program" target="_blank"><span>Veřejný program</span></a>&nbsp;|&nbsp;
					<a href="<?php echo HTTP_DIR."remote/logout.php?_return=admin/"; ?>" title="odhlásit se"><span>Odhlásit se</span></a>
					<div style="margin-top:5px;">
			   uživatel: <span style="font-weight:bold;"><?php echo $data['user']['publicname']; ?></span><br />
			   poslední přihlášení: <?php echo date("j. n. Y H:i:s",$data['user']['activitytime']); ?> |
			   ip adresa: <?php echo gethostbyname($_SERVER['HTTP_HOST'])." - ".$_SERVER['HTTP_HOST']; ?> |
			   počet přihlášení: <?php echo $data['user']['logincounter']; ?>
					</div>
				</div>
			</div>
		</div>
		<!-- end of headerCanvas -->
		<!-- start of siteCanvas -->
		<div id="siteCanvas">
			<div id="siteHeader">
				<?php
					$meeting = $data['database']->query('SELECT	place, DATE_FORMAT(start_date, "%Y") AS year
						FROM kk_meetings
						WHERE id = ? AND deleted = ?
						LIMIT 1', $_SESSION['meetingID'], '0')->fetch();
				 ?>
				<h1>Srazy VS :::: <?php echo $meeting['place']." ".$meeting['year']; ?></h1>
			</div>
			<div id="siteNavbar">
				<a href="<?php echo MEET_DIR."/?mid=".$_SESSION['meetingID']; ?>" title="sraz">
					<img src="<?php echo IMG_DIR; ?>home.png" alt="home" /> sraz</a> |
				<a href="<?php echo BLOCK_DIR; ?>" title="bloky">
					<img src="<?php echo IMG_DIR; ?>blocks.png" alt="blok" /> bloky</a> |
				<a href="<?php echo PROG_DIR; ?>" title="programy">
					<img src="<?php echo IMG_DIR; ?>programs.png" alt="program" /> programy</a> |
				<a href="<?php echo VISIT_DIR; ?>" title="účastníci">
					<img src="<?php echo IMG_DIR; ?>users.png" alt="ucastnik" /> účastníci</a> |
				<a href="<?php echo EXP_DIR; ?>" title="exporty">
					<img src="<?php echo IMG_DIR; ?>exports.png" alt="exporty" /> exporty</a> |
				<a href="<?php echo CAT_DIR; ?>" title="kategorie">
					<img src="<?php echo IMG_DIR; ?>categories.png" alt="kategorie" /> kategorie</a>
			</div>
			<?php echo $data['menu']; ?>
   			<!-- start of siteContent -->
   			<div id='siteContent'>
				<div id='content'>
