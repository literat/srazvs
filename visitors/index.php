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
$search = requested("search","");
$error = requested("error","");

$Container = new Container($GLOBALS['cfg'], $mid);
$VisitorsHandler = $Container->createVisitor();
$ExportHandler = $Container->createExport();

if(isset($_POST['checker'])){
	$id = $_POST['checker'];
	$query_id = NULL;
	foreach($id as $key => $value) {
		$query_id .= $value.',';
	}
	$query_id = rtrim($query_id, ',');
}
else {
	$query_id = $id;	
}

switch($cms) {
	// delete visitor
	case "del":
		if($VisitorsHandler->delete($query_id)){	
	  		redirect("index.php?error=del");
		}
		break;
	// pay full charge
	case "pay":
		if($return = $VisitorsHandler->payCharge($query_id, 'cost')) {
			redirect("index.php?error=mail_send");
		} else {
			if($return == 'already_paid') {
				$error = $return;	
			} else {
				echo 'Došlo k chybě při odeslání e-mailu.';
				echo 'Chybová hláška: ' . $return;
			}
		}
		break;
	// pay advance	
	case "advance":
		if($return = $VisitorsHandler->payCharge($query_id, 'advance')) {
			redirect("index.php?error=mail_send");
		} else {
			if($return == 'already_paid') {
				$error = $return;	
			} else {
				echo 'Došlo k chybě při odeslání e-mailu.';
				echo 'Chybová hláška: ' . $return;
			}
		}
		break;
	// searching
	case "search":
		if(isset($search)){
			$VisitorsHandler->setSearch($search);
		}
		break;
	// export all visitors to excel
	case "export":
		$ExportHandler->printVisitorsExcel();
		break;
}

################## GENEROVANI STRANKY #############################

include_once($INCDIR.'http_header.inc.php');
include_once($INCDIR.'header.inc.php');

?>

<div class='siteContentRibbon'>Správa účastníků</div>
<?php printError($error); ?>
<div class='pageRibbon'>informace a exporty</div>
<div>
	počet účastníků: <span style="font-size:12px; font-weight:bold;"><?php echo $VisitorsHandler->getCount(); ?></span>
	<span style="margin-left:10px; margin-right:10px;">|</span> 
	<a style='text-decoration:none; padding-right:2px;' href='?cms=export'>
		<img style='border:none;' align='absbottom' src='<?php echo $ICODIR; ?>small/xlsx.png' />
		export účastníků
	</a>
</div>

<div class='pageRibbon'>seznam účastníků</div>
<div class='link'>
	<form action='index.php' method='post'>
		<label>hledej:</label>
		<input type='text' name='search' size='30' value='<?php echo $search; ?>' />
        <button type='submit' onClick=\"this.form.submit()\">
  			<img src='<?php echo $ICODIR; ?>small/search.png' /> Hledej
		</button>
 		<!--<button type='button' onclick=\"window.location.replace('index.php')\">
  		<img src='".$ICODIR."small/storno.png'  /> Zpět</button>-->
		<input type='hidden' name='cms' value='search' />
	</form>
	<a class='link' href='process.php?cms=new&page=visitors'>
		<img src='<?php echo $ICODIR; ?>small/new.png' />NOVÝ ÚČASTNÍK
	</a>
</div>

<script src='<?php echo $JSDIR; ?>jquery/jquery.tablesorter.min.js' type='text/javascript'></script>
<script>
$(document).ready(function() {
	$("#VisitorsTable").tablesorter( {
		headers: {
			0: { sorter: false},
			1: { sorter: false},
			2: { sorter: false},
			3: { sorter: false},
			4: { sorter: false},
			5: { sorter: false},
			12: { sorter: false},
		}
	} );
} );
</script>

<?php
###################################################################

// render data table
echo $VisitorsHandler->renderData();

###################################################################

include_once($INCDIR.'footer.inc.php');

###################################################################
?>