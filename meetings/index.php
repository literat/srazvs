<?php

require_once('../inc/define.inc.php');

######################### PRISTUPOVA PRAVA ################################

include_once($INCDIR.'access.inc.php');

###########################################################################

function getPrograms($id){
	$sql = "SELECT 	progs.id AS id,
					progs.name AS name,
					style
			FROM kk_programs AS progs
			LEFT JOIN kk_categories AS cat ON cat.id = progs.category
			WHERE block='".$id."' AND progs.deleted='0'
			LIMIT 10";
	$result = mysql_query($sql);
	$rows = mysql_affected_rows();

	if($rows == 0) $html = "";
	else{
		$html = "<table class='programs'>\n";
		$html .= " <tr>\n";
		while($data = mysql_fetch_assoc($result)){			
			$html .= "<td class='cat-".$data['style']."' style='text-align:center;'>\n";
			$html .= "<a class='program' href='../programs/process.php?id=".$data['id']."&cms=edit&page=meetings' title='".$data['name']."'>".$data['name']."</a>\n";
			$html .= "</td>\n";
		}
		$html .= " </tr>\n";
		$html .= "</table>\n";
	}
	return $html;
}

################################ SQL, KONTROLA #############################

$mid = requested("mid","");
if($mid == "") {
	$mid = $_SESSION['meetingID'];	
} else {
	$_SESSION['meetingID'] = $mid;
}

$sql = "SELECT	*
		FROM kk_meetings
		WHERE id='".$mid."' AND deleted='0'
		LIMIT 1";
$result = mysql_query($sql);
$data = mysql_fetch_assoc($result);

$cms = requested("cms","");
$error = requested("error","");

$place = requested("place",$data['place']);
$start_date = requested("start_date",$data['start_date']);
$end_date = requested("end_date",$data['end_date']);
$open_reg = requested("open_reg",$data['open_reg']);
$close_reg = requested("close_reg",$data['close_reg']);
$contact = requested("contact",$data['contact']);
$email = requested("email",$data['email']);
$gsm = requested("gsm",$data['gsm']);
$cost = requested("cost",$data['cost']);
$advance = requested("advance",$data['advance']);
$numbering = requested("numbering",$data['numbering']);

////inicializace promenych
$error_start = "";
$error_end = "";
$error_open_reg = "";
$error_close_reg = "";
$error_login = "";

################################## PROGRAM #####################################

$html = "";
$days = array("pátek", "sobota", "neděle");

foreach($days as $dayKey => $dayVal){
	$html .= "<table class='blocks'>\n";
	$html .= " <tr>\n";
	$html .= "  <td class='day' colspan='2' >".$dayVal."</td>\n";
	$html .= " </tr>\n";

	$sql = "SELECT 	blocks.id AS id,
					day,
					DATE_FORMAT(`from`, '%H:%i') AS `from`,
					DATE_FORMAT(`to`, '%H:%i') AS `to`,
					blocks.name AS name,
					program,
					style
			FROM kk_blocks AS blocks
			LEFT JOIN kk_categories AS cat ON cat.id = blocks.category
			WHERE blocks.deleted = '0' AND day='".$dayVal."' AND meeting='".$mid."'
			ORDER BY `from` ASC";

	$result = mysql_query($sql);
	$rows = mysql_affected_rows();

	if($rows == 0){
		$html .= "<td class='emptyTable' style='width:400px;'>Nejsou žádná aktuální data.</td>\n";
	}
	else{
		while($data = mysql_fetch_assoc($result)){
			$html .= "<tr>\n";
			$html .= "<td class='time'>".$data['from']." - ".$data['to']."</td>\n";
			if($data['program'] == 1){ 
				$html .= "<td class='cat-".$data['style']."'>\n";
			 	$html .= "<div>\n";
			  	$html .= "<a class='block' href='".$BLOCKDIR."process.php?id=".$data['id']."&cms=edit&page=meetings' title='".$data['name']."'>".$data['name']."</a>\n";
			 	$html .= "</div>\n";
				$html .= getPrograms($data['id']);
				$html .= "</td>\n";
			}
			else {
				$html .= "<td class='cat-".$data['style']."'>";
				$html .= "<a class='block' href='".$BLOCKDIR."process.php?id=".$data['id']."&cms=edit&page=meetings' title='".$data['name']."'>".$data['name']."</a>\n";
				$html .= "</td>\n";
			}
			$html .= "</tr>\n";
		}
	}
	$html .= "</table>\n";
}

$html .= "  <div class='pageRibbon'>nastavení</div>\n";

################## VLOZENE STYLY ##################################

$style = getCategoryStyle();

############################## GENEROVANI STRANKY ##########################

include_once($INCDIR.'http_header.inc.php');
include_once($INCDIR.'header.inc.php');

?>

<div class='siteContentRibbon'>Aktuální sraz</div>
<?php printError($error); ?>
<div class='pageRibbon'>program</div>
<?php echo $html; ?>

<form action='update.php?redir=index' method='post'>

<div class='button-line'>
 <button type='submit' onclick=\"this.form.submit()\">
  <img src='<?php echo $ICODIR; ?>small/save.png' /> Uložit</button>
 <button type='button' onclick="window.location.replace('list.php')">
  <img src='<?php echo $ICODIR; ?>small/storno.png'  /> Storno</button>
</div>

<!-- Datedit by Ivo Skalicky - ITPro CZ - http://www.itpro.cz -->
<script type="text/javascript" charset="iso-8859-1" src="<?php echo $AJAXDIR; ?>datedit/datedit.js"></script>
<script type="text/javascript" charset="utf-8" src="<?php echo $AJAXDIR; ?>datedit/lang/cz.js"></script>
<script type="text/javascript">
  <?php
  //jak prekonvertovat pomoci datedit datum pro sql databazi
  //datedit("start_date","dd.mm.yyyy",true,"yyyy-mm-dd");
  ?>
  datedit("start_date","yyyy-mm-dd");
  datedit("end_date","yyyy-mm-dd");
  datedit("open_reg","yyyy-mm-dd HH:MM:SS");
  datedit("close_reg","yyyy-mm-dd HH:MM:SS");
</script> 

<table class='form'>
 <tr>
  <td class='label'><label class="required">Místo:</label></td>
  <td><input type='text' name='place' size='30' value='<?php echo $place; ?>' /></td>
 </tr>
 <tr>
  <td class='label'><label class="required">Začátek</label></td>
  <td><div class="picker"><input id='start_date' type='text' size='20' name='start_date' value="<?php echo $start_date; ?>" /></div><?php printError($error_start); ?>(rrrr-mm-dd)</td>
 </tr>
 <tr>
  <td class='label'><label class="required">Konec:</label></td>
  <td><div class="picker"><input id='end_date' type='text' size='20' name='end_date' value="<?php echo $end_date; ?>" /></div><?php printError($error_end); ?>(rrrr-mm-dd)</td>
 </tr>
 <tr>
  <td class='label'><label class="required">Otevřít přihlašování:</label></td>
  <td><div class="picker"><input id='open_reg' type='text' size='30' name='open_reg' value="<?php echo $open_reg; ?>" />
  
  

  
  </div>
  
  <span class="timediv l">
            <a href="#" onclick="javascript:return GetTime(event,'+');" ondblclick="javascript:return GetTime(event,'+');" class="btn-up l" title="zvýšit">
                <img src="<?php echo $ICODIR; ?>small/btn-up.png" alt="zvýšit" width="17" height="10" />
            </a>
            <a href="#" onclick="javascript:return GetTime(event,'-');" ondblclick="javascript:return GetTime(event,'-');" class="btn-down l" title="snížit">
                <img src="<?php echo $ICODIR; ?>small/btn-down.png" alt="snížit" width="17" height="10" />
            </a>
  </span>
  
  <?php printError($error_open_reg); ?>(rrrr-mm-dd hh:mm:ss)</td>
 </tr>
 <tr>
  <td class='label'><label class="required">Zavřít přihlašování:</label></td>
  <td><div class="picker"><input id='close_reg' type='text' size='30' name='close_reg' value="<?php echo $close_reg; ?>" /></div><?php printError($error_close_reg); ?>(rrrr-mm-dd hh:mm:ss)</td>
 </tr>
 <tr>
  <td class='label'><label>Cena srazu:</label></td>
  <td><input type='text' size='30' name='cost' value="<?php echo $cost; ?>" /> ,- Kč</td>
 </tr>
 <tr>
  <td class='label'><label>Záloha:</label></td>
  <td><input type='text' size='30' name='advance' value="<?php echo $advance; ?>" /> ,- Kč</td>
 </tr> 
 <tr>
 <tr>
  <td class='label'><label>Číslování dokladů:</label></td>
  <td><input type='text' size='30' name='numbering' value="<?php echo $numbering; ?>" /></td>
 </tr> 
 <tr>
  <td class='label'><label>Kontaktní osoba:</label></td>
  <td><input type='text' size='30' name='contact' value="<?php echo $contact; ?>" /><?php printError($error_close_reg); ?></td>
 </tr> 
 <tr>
  <td class='label'><label>E-mail:</label></td>
  <td><input type='text' size='30' name='email' value="<?php echo $email; ?>" /><?php printError($error_close_reg); ?></td>
 </tr> 
 <tr>
  <td class='label'><label>Telefon (mobil):</label></td>
  <td><input type='text' size='30' name='gsm' value="<?php echo $gsm; ?>" /><?php printError($error_close_reg); ?> (123456789)</td>
 </tr> 
</table>

 <input type='hidden' name='cms' value='update'>
 <input type='hidden' name='mid' value='<?php echo $mid; ?>'>
</form>

<?php

###################################################################

include_once($INCDIR.'footer.inc.php');

###################################################################
?>