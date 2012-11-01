<?php
//header
require_once('../inc/define.inc.php');

###################### PRISTUPOVA PRAVA ###########################

include_once($INCDIR.'access.inc.php');

########################## KONTROLA ###############################

$mid = $_SESSION['meetingID'];
$id = requested("id","");
$cms = requested("cms","");
$error = requested("error","");

$CategoryHandler = new Category();

######################### DELETE CATEGORY #########################

if($cms == "del"){
	if($CategoryHandler->delete($id)){	
	  	redirect("index.php?error=del");
	}
}

################## GENEROVANI STRANKY #############################

// styly jednotlivych kategorii
$style = $CategoryHandler->getStyles();

include_once($INCDIR.'http_header.inc.php');
include_once($INCDIR.'header.inc.php');

?>

<div class='siteContentRibbon'>Správa kategorií</div>
<div class='pageRibbon'>seznam kategorií</div>

<?php printError($error); ?>

<div class='link'>
	<a class='link' href='process.php?cms=new'>
    	<img src='<?php echo $ICODIR; ?>small/new.png' />NOVÁ KATEGORIE</a>
</div>

<script src='<?php echo $JSDIR ?>jquery/jquery.tablesorter.min.js' type='text/javascript'></script>
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

include_once($INCDIR.'footer.inc.php');

###################################################################
?>