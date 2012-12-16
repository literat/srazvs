<?php
//header
require_once('../inc/define.inc.php');

######################### PRISTUPOVA PRAVA ################################

include_once($INCDIR.'access.inc.php');

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
$BlocksHandler = $Container->createBlock();

// delete block
if($cms == "del"){
	if($BlocksHandler->delete($id)){	
	  	redirect("index.php?error=del");
	}
}

// styles in header
$style = Category::getStyles();

################## GENEROVANI STRANKY #############################

include_once($INCDIR.'http_header.inc.php');
include_once($INCDIR.'header.inc.php');

?>

<div class='siteContentRibbon'>Správa bloků</div>
<?php printError($error); ?>
<div class='pageRibbon'>seznam bloků</div>
<div class='link'>
	<a class='link' href='process.php?cms=new'>
    	<img src='<?php echo $ICODIR; ?>small/new.png' />NOVÝ BLOK
	</a>
</div>

<script src='<?php echo $JSDIR ?>jquery/jquery.tablesorter.min.js' type='text/javascript'></script>
<script>
$(document).ready(function() {
	$("#BlocksTable").tablesorter( {
		headers: {
			0: { sorter: false},
			1: { sorter: false},
			7: { sorter: false},
			10: { sorter: false},
		}
	} );
} );
</script>

<?php
###################################################################

// render data table
echo $BlocksHandler->renderData();

###################################################################

include_once($INCDIR.'footer.inc.php');

###################################################################
?>