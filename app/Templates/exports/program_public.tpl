<head>
	<style>
		body {
			font-family:Arial,Geneva,Sans-Serif;
			font-size:15px;
			text-align:center;
		}

		table {
			border-collapse:collapse;
			width:100%;
		}

		td {
			width:100%;
			text-align:center;
			padding:3px 0 0 0;
		}

		td.day {
			border:1px solid black;
			background-color:#777777;
			width:80px;
		}

		td.time {
			border:1px solid black;
			background-color:#cccccc;
			width:100px;
		}
		<?php echo $data['styles']; ?>
	</style>
</head>
<body>
	<h2><?php echo $data['header']; ?></h2>
	<h4>program srazu vodních skautů</h4>

	<table>
		<?php
			$days = array("pátek", "sobota", "neděle");
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
						<td class='category cat-<?php echo $row['style']; ?>' style='border:1px solid black;'>
							<div><?php echo $row['name']; ?></div>
							<?php echo ProgramModel::getProgramsLarge($row['id'], $data['database']); ?>
						</td>
					</tr>
					<?php } ?>
		<?php } ?>
	</table>
</body>
