<?php echo $data['header']; ?>
<body>
	<table class='summary'>
		<?php
			$i = 0;
			foreach($data['result'] as $row) {

			if($i % 44 == 0){
		?>
		<tr>
			<td class='header'>Příjmení a Jméno</td>
			<td class='header'>Narození</td>
			<td class='header'>Adresa</td>
			<td class='header'>Jednotka</td>
			<td class='header'>Záloha</td>
			<td class='header'>Doplatek</td>
			<td class='header'>Podpis</td>
		</tr>
		<?php } ?>
		<tr>
			<td class='name'>
				<?php echo $row['surname']." ".$row['name']; ?>
			</td>
			<td class='birthday'>
				<?php echo $row['birthday']; ?>
			</td>
			<td class='address'>
				<?php echo $row['street'].", ".$row['city'].", ".$row['postal_code']; ?>
			</td>
			<td class='group'>
				<?php echo $row['group_num']; ?>
			</td>
			<td>
				<?php echo $row['bill']; ?>
			</td>
			<td>
				<?php echo $row['balance']; ?>
			</td>
			<td class='signature'>&nbsp;</td>
		</tr>
		<?php
			$i++;
			}
		?>
	</table>
</body>
