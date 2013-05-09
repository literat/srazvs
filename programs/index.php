<?php
//header
require_once('../inc/define.inc.php');

######################### PRISTUPOVA PRAVA ################################

include_once(INC_DIR.'access.inc.php');

########################### POST a GET #########################

if($mid = requested("mid","")){
	$_SESSION['meetingID'] = $mid;
} else {
	$mid = $_SESSION['meetingID'];
}

$id = requested("id","");
$cms = requested("cms","");
$error = requested("error","");

$Container = new Container($GLOBALS['cfg'], $mid);
$ProgramsHandler = $Container->createProgram();

// delete program
if($cms == "del"){
	if($ProgramsHandler->delete($id)){	
	  	redirect("index.php?error=del");
	}
}

// styles in header
$style = CategoryModel::getStyles();

################## GENEROVANI STRANKY #############################

include_once(INC_DIR.'http_header.inc.php');
include_once(INC_DIR.'header.inc.php');

########################## GENEROVANI STRANKY #############################

?>

<div class='siteContentRibbon'>Správa programů</div>
<?php printError($error); ?>
<div class='pageRibbon'>seznam programů</div>
<div class='link'>
	<a class='link' href='process.php?cms=new'>
    	<img src='<?php echo IMG_DIR; ?>icons/new.png' />NOVÝ PROGRAM
	</a>
</div>

<script src='<?php echo JS_DIR ?>jquery/jquery.tablesorter.min.js' type='text/javascript'></script>
<script>
$(document).ready(function() {
	$("#ProgramsTable").tablesorter( {
		headers: {
			0: { sorter: false},
			1: { sorter: false},
			2: { sorter: false},
			4: { sorter: false},
			9: { sorter: false},
		}
	} );
} );
</script>

<?php

// render data table
echo $ProgramsHandler->renderData();

###################################################################

include_once(INC_DIR.'footer.inc.php');

###################################################################
?>