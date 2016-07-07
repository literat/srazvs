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
			while($row = mysql_fetch_assoc($data['result'])){
				if($i % 44 == 0){
        ?>
		<tr>
			<td class="header">Příjmení a Jméno</td>
			<td class="header">Přezdívka</td>
			<td class="header">Podpis</td>
		</tr>
		<?php } ?>
		<tr>
			<td class="name"><?php echo $row['surname']." ".$row['name']; ?></td>
			<td class="birthday"><?php echo $row['nick']; ?></td>
			<td class="signature">&nbsp;</td>
		</tr>
		<?php
				$i++;
			}
        ?>
	</table>
</body>
