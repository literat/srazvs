<?php
//header
require_once('../inc/define.inc.php');

###################### PRISTUPOVA PRAVA ###########################

include_once(INC_DIR.'access.inc.php');

########################## KONTROLA ###############################

$mid = $_SESSION['meetingID'];
$id = requested("id","");
$cms = requested("cms","");
$error = requested("error","");

$Container = new Container($GLOBALS['cfg'], $mid);
$CategoryHandler = $Container->createCategory();

######################### DELETE CATEGORY #########################

if($cms == "del"){
	if($CategoryHandler->delete($id)){	
	  	redirect("index.php?error=del");
	}
}

################## GENEROVANI STRANKY #############################

// styly jednotlivych kategorii
$style = $CategoryHandler->getStyles();

include_once(INC_DIR.'http_header.inc.php');
include_once(INC_DIR.'header.inc.php');

?>

<div class='siteContentRibbon'>Správa kategorií</div>
<div class='pageRibbon'>seznam kategorií</div>

<?php printError($error); ?>

<div class='link'>
	<a class='link' href='process.php?cms=new'>
    	<img src='<?php echo IMG_DIR; ?>icons/new.png' />NOVÁ KATEGORIE</a>
</div>

<script src='<?php echo JS_DIR ?>jquery/jquery.tablesorter.min.js' type='text/javascript'></script>
<script>
$(document).ready(function() {
	$("#CategoryTable").tablesorter( {
		headers: {
			0: { sorter: false},
			1: { sorter: false},
			3: { sorter: false}
		}
	} );
} );
</script>

<?php
// vypisu kategorie
echo $CategoryHandler->render();

###################################################################

include_once(INC_DIR.'footer.inc.php');

###################################################################
?>