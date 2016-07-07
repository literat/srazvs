<head>
	<style>
		body {
			font-family:Verdana,Arial,Geneva,Sans-Serif;
			font-size:10px;
			text-align:left;
		}

		table {
			border-collapse:collapse;
			width:100%;
		}

		td {
			text-align:left;
			width:375px;
			_border:1px solid black;
		}

		.cutLine {border: 2px dotted black; vertical-align:top;}

		.nick {font-size:17px;color:navy;}
		.name {font-size:14px;}
		.progPart {height:207px;padding-left:5px;}

		.program{font-size:13px;color:black;}

		.meeting{font-size:13px; color:#4169e1;}
		.block, .group_name {color:grey;}
		.name, .group_name, .meeting {text-align:right;}
		.program, .nick {font-weight:bold;}
	</style>
</head>
<body>
<?php
$i = 0;
foreach($data['result'] as $row) {
	if($i%2 == 0){ ?>
		<table>
			<tr>
				<td class='cutLine'>
	<?php } else { ?>
		<td class='cutLine'>
	<?php } ?>
	<table>
		<tr>
			<td class='name'>
				<span class='nick'><?php echo $row['nick']; ?></span> <?php echo $row['name']." ".$row['surname']; ?>";
			</td>
		</tr>
		<tr>

		</tr>
			<?php echo \App\ExportModel::getPdfBlocks($row['id'], $data['database']); ?>
		<tr>
			<td class='meeting'>
				SRAZ VS - <?php echo $row['place']." ".$row['start_date']." ".$row['end_date']; ?>
			</td>
		</tr>
	</table>

	<?php if($i%2 == 0){ ?>
		</td>
	<?php } else { ?>
		</td>
	</tr>
</table>
<?php }
	$i++;
}

if($i%2 != 0){
?>
			<td class='cutLine'></td>
		</tr>
	</table>
<?php } ?>
</body>
