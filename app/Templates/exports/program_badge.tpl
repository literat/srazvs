<head>
	<style>
		body {
			font-family:Verdana,Arial,Geneva,Sans-Serif;
			font-size:9px;
			text-align:left;
		}

		table {
			border-collapse:collapse;
			width:100%;
		}

		td {
			text-align:left;
			_width:375px;
			_border:1px solid black;
		}

		.cutLine {border: 2px dotted black; vertical-align:top;}

		.day {font-size:12px;font-weight:bold;margin-top:15px;}
		.time {color:grey;}
		.meal {font-size:10px;font-weight:bold;}

		.program{font-size:13px;color:black;}

		.meeting{font-size:13px; color:#4169e1;}
		.block, .group_name {color:grey;}
		.name, .group_name, .meeting {text-align:right;}
		.program, .nick {font-weight:bold;}
	</style>
</head>
<body>

<?php
$days = array("PÁTEK", "SOBOTA", "NEDĚLE");

$i = 0;
foreach($data['result'] as $unused) {

	if($i % 2 == 0) {
?>
	<table>
		<tr>
			<td class='cutLine'>
	<?php }	else { ?>
			<td class='cutLine'>
	<?php } ?>
	<table>
		<tr>
			<td>
				<table>
				<?php
				foreach($days as $day_key => $day_val) {
				?>
					<tr>
						<td class='day'><?php echo $day_val; ?></td>
					</tr>
				<?php
		$result = BlockModel::getExportBlocks($data['meeting_id'], $day_val, $data['database']);

		if(!$result) {
			?>
			<td class='emptyTable' style='width:400px;'>Nejsou žádná aktuální data.</td>
			<?php
		} else {
			foreach($result as $row) {
				?>
				<tr>
				<?php
				if($row['category'] == 7) {
				?>
					<td>
						<span class='meal'>
						<?php echo $row['from']." - ".$row['to']." ";
				} else { ?>
					<td>
						<span class='time'><?php echo $row['from']." - ".$row['to']; ?>
						</span>
				<?php }

				// kdyz je programovy blok, tak zobrazim jenom jeho obsah
				if($row['program']) {
					echo ProgramModel::getProgramNames($row['id'], $data['database']); ?>
					</td>
					<?php
				}
				else {
					if($row['category'] == 7) {
						echo $row['name']; ?>
						</span></td>
						<?php
					} else {
						echo $row['name']; ?>
						</td>
						<?php
					}
				}
				?>
					</tr>
				<?php
				if($day_val == "SOBOTA" && $row['name'] == "Oběd") {
				?>
						</table>
					</td>
					<td>
						<table>
				<?php
				}
			}
		}
	}
				?>
				</table>

			</td>
		</tr>
	</table>

	<?php if($i % 2 == 0) { ?>
			</td>
	<?php } else { ?>
			</td>
		</tr>
	</table>
	<?php }

	$i++;
	if($i == 8) break;
}
?>

</body>
