<head>
	<style>
		body {
			font-family:Arial,Geneva,Sans-Serif;
			text-align:center;
			vertical-align:middle;
		}

		h1 {font-size:70px;padding:0px;margin:0px;}
		h2 {font-size:40px;padding:0px;margin:0px;}

		table {
			border-collapse:collapse;
			width:100%;
			vertical-align:top;
		}

		td {
			width:100%;
			padding-left:30px;
			height:90px;
			font-size:60px;
			/*border:1px solid black;*/
			text-align:middle;
		}

		td.day {
			border:1px solid black;
			background-color:#000;
			width:100%;
			text-align:center;
			height:50px;
			color:#fff;
		}

		td.time {
			border:1px solid black;
			background-color:#dddddd;
			width:500px;
			text-align:center;
			vertical-align:middle;
			font-weight:bold;
			font-size:50px;
		}
	</style>
</head>
<body>
	<h1><?php echo $data['header']; ?></h1>
	<h2>program srazu vodních skautů</h2>

	<table>
		<?php
			$days = array("PÁTEK", "SOBOTA", "NEDĚLE");
			foreach($days as $day) {
			?>
					<tr>
						<td class='day' colspan='2' ><?php echo $day; ?></td>
					</tr>
					<?php
					$result = \App\ExportModel::getLargeProgramData($data['meeting_id'], $day, $data['database']);
					foreach($result as $row) {
					?>
					<tr>
						<td class='time'><?php echo $row['from']." - ".$row['to']; ?></td>
						<td style='border:1px solid black;'>
							<div><?php echo $row['name']; ?></div>
							<?php echo ProgramModel::getProgramsLarge($row['id'], $data['database']); ?>
						</td>
					</tr>
					<?php } ?>
		<?php } ?>
	</table>
</body>
