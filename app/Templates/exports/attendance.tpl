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
	<?php echo $data['attendance']; ?>
    <table>
		<?php
			$i = 0;
			while($row = mysql_fetch_assoc($data['result'])){
				if($i % 44 == 0){ 
        ?>
		<tr>
			<td class="header">Příjmení a Jméno</td>
			<td class="header">Narození</td>
			<td class="header">Adresa</td>
			<td class="header">Středisko/Přístav</td>
			<td class="header">Podpis</td>
		</tr>
		<?php } ?>
		<tr>
			<td class="name"><?php echo $row['surname']." ".$row['name']; ?></td>
			<td class="birthday"><?php echo $row['birthday']; ?></td>
			<td class="address"><?php echo $row['street'].", ".$row['city'].", ".$row['postal_code']; ?></td>
			<td class="group"><?php echo $row['group_num'].", ".$row['group_name']; ?></td>
			<td class="signature">&nbsp;</td>
		</tr>
		<?php	
				$i++;
			}
        ?>
	</table>
</body>