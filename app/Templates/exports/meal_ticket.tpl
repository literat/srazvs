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
			text-align:center;
			border-right:1px dotted black;
			vertical-align:middle;
		}
		
		img {height:32px;}
		
		.cutLine {border: 2px dotted black; vertical-align:top;}
		
		.nick {font-size:17px;color:navy;}
		
		.meal {font-weight:bold;}
		
		td.name {
			font-size:14px;
			width:220px;
			padding-right:5px;
		}
		
		.name, .group_name, .meeting {text-align:right;}
		.program, .nick {font-weight:bold;}
	</style>
</head>
<body>
<?php
foreach($data['result'] as $row) {
	if($row['fry_dinner'] == "ano") $imgUrl1 = IMG_DIR."meals/dinner.png";
	else $imgUrl1 = IMG_DIR."meals/nomeal.png";
	if($row['sat_breakfast'] == "ano") $imgUrl2 = IMG_DIR."meals/breakfast.png";
	else $imgUrl2 = IMG_DIR."meals/nomeal.png";
	if($row['sat_lunch'] == "ano") $imgUrl3 = IMG_DIR."meals/lunch.png";
	else $imgUrl3 = IMG_DIR."meals/nomeal.png";
	if($row['sat_dinner'] == "ano") $imgUrl4 = IMG_DIR."meals/dinner.png";
	else $imgUrl4 = IMG_DIR."meals/nomeal.png";
	if($row['sun_breakfast'] == "ano") $imgUrl5 = IMG_DIR."meals/breakfast.png";
	else $imgUrl5 = IMG_DIR."meals/nomeal.png";
	if($row['sun_lunch'] == "ano") $imgUrl6 = IMG_DIR."meals/lunch.png";
	else $imgUrl6 = IMG_DIR."meals/nomeal.png";
?>
	<table class='cutLine'>
	<tr>
	<td><div>Pátek</div><div><img src='<?php echo $imgUrl1; ?>' /></div><div class='meal'>Večeře</div></td>
	<td><div>Sobota</div><div><img src='<?php echo $imgUrl2; ?>' /></div><div class='meal'>Snídaně</div></td>
	<td><div>Sobota</div><div><img src='<?php echo $imgUrl3; ?>' /></div><div class='meal'>Oběd</div></td>
	<td><div>Sobota</div><div><img src='<?php echo $imgUrl4; ?>' /></div><div class='meal'>Večeře</div></td>
	<td><div>Neděle</div><div><img src='<?php echo $imgUrl5; ?>' /></div><div class='meal'>Snídaně</div></td>
	<td><div>Neděle</div><div><img src='<?php echo $imgUrl6; ?>' /></div><div class='meal'>Oběd</div></td>
	<td class='name'><span class='nick'><?php echo $row['nick']; ?></span><?php echo $row['name'].' '.$row['surname']; ?></td>
	</tr>
    </table>
<?php } ?>
</body>