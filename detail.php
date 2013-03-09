<?php
require_once('inc/define.inc.php');

$id = requested("id","");
$type = requested("type","");

$sql = "SELECT	*
		FROM kk_".$type."s
		WHERE id='".$id."' AND deleted='0'
		LIMIT 1";
$result = mysql_query($sql);
$data = mysql_fetch_assoc($result);

$name = requested("name",$data['name']);
$description = requested("description",$data['description']);
$tutor = requested("tutor",$data['tutor']);
$email = requested("email",$data['email']);
if($type == "program"){
	$capacity = requested("capacity",$data['capacity']);
	
	$countSql = "SELECT COUNT(visitor) AS visitors
				 FROM `kk_visitor-program` AS visprog 
				 LEFT JOIN kk_visitors AS vis ON vis.id = visprog.visitor
				 WHERE program = '".$data['id']."' AND vis.deleted = '0'";
	$countResult = mysql_query($countSql);
	$countData = mysql_fetch_assoc($countResult);
	
	$html = "<tr>\n";
	$html .= " <td class=\"label\">Obsazenost programu:</td>\n";
	$html .= " <td class=\"text\">".$countData['visitors']."/".$capacity."</td>\n";
    $html .= "</tr>\n";
}
else $html = "";

?>
<html><head><meta http-equiv="Content-Type" content="text/html; charset=<?php echo $cfg['http-encoding'] ?>" /></head><body><style>td.text {text-align:left;}</style><table class="form"><tr><td class="label">Program:</td><td class="text"><?php echo $name; ?></td></tr><tr><td class="label">Popis:</td><td class="text"><?php echo $description; ?></td></tr><tr><td class="label">Lektor:</td><td class="text"><?php echo $tutor; ?></td></tr><tr><td class="label">E-mail:</td><td class="text"><a href="mailto:<?php echo $email; ?>" title="e-mail"><?php echo $email; ?></a></td></tr><?php echo $html; ?></table></body></html>