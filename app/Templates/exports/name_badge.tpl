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
			width:500px;
			_border:1px solid black;
		}
		
		h1 {font-size:70px;color:#555;}
		
		.cutLine {border: 2px dotted black; vertical-align:top;}
		.nick {height:205px;vertical-align:middle;text-align:center;}
	</style>
</head>
<body>
<?php
$i = 0;
while($row = mysql_fetch_assoc($data['result'])){
	if($i%2 == 0){
?>	
	<table>
		<tr>
		  <td class='cutLine'>
<?php }	else { ?>
	<td class='cutLine'>
<?php } ?>
	<table>
		<tr>
			<td class='nick'>
				<h1><?php echo $row['nick']; ?></h1>
			</td>
		</tr>
	</table>
<?php if($i%2 == 0){ ?>
	</td>
<?php }	else { ?>
			</td>
		</tr>
	</table>	
<?php }
	$i++;
} ?>
</body>