<head>
	<style>
		body {
			font-family:Arial,Geneva,Sans-Serif;
			text-align:left;
		}

		table {
			border-collapse:collapse;
			width:100%;
		}

		td {
			padding:5px;
			border:1px solid black;
			font-size:9px;
		}

		.signature {width:80px;}
		.header{color:white;background-color:black;}
	</style>
</head>
<body>
	<table>
		<?php
			$i = 0;
			foreach($data['result'] as $row) {
				if($i % 44 == 0){
		?>
		<tr>
			<td class="header">Příjmení, Jméno, Přezdívka</td>
			<td class="header">Adresa</td>
			<td class="header">Středisko/Přístav</td>
		</tr>
		<?php } ?>
		<tr>
			<td class="name"><?php echo $row['surname']." ".$row['name']." ".$row['nick']; ?></td>
			<td class="address"><?php echo $row['street'].", ".$row['city'].", ".$row['postal_code']; ?></td>
			<td class="group"><?php echo $row['group_num'].", ".$row['group_name']; ?></td>
		</tr>
		<?php
				$i++;
			}
		?>
	</table>
</body>
