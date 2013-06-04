<?php
/**
 * This file handles the retrieval and serving of news articles
 */
class MeetingController
{
	/**
	 * This template variable will hold the 'view' portion of our MVC for this 
	 * controller
	 */
	public $template = 'meeting';

	/**
	 * This is the default function that will be called by router.php
	 * 
	 * @param array $getVars the GET variables posted to index.php
	 */
	public function init(array $getVars)
	{
		######################### PRISTUPOVA PRAVA ################################

		include_once(INC_DIR.'access.inc.php');

		###########################################################################

		if($mid = requested("mid","")){
			$_SESSION['meetingID'] = $mid;
		} else {
			$mid = $_SESSION['meetingID'];
		}

		$id = requested("id",$mid);
		$cms = requested("cms","");
		$error = requested("error","");

		$Container = new Container($GLOBALS['cfg'], $mid);
		$MeetingsHandler = $Container->createMeeting();
		$ViewHandler = $Container->createView();

		// delete program
		if($cms == "del"){
			if($MeetingsHandler->delete($id)){	
			  	redirect("index.php?error=del");
			}
		}

		if($cms == 'list-view'){
			$heading1 = 'Správa srazů';
			$heading2 = 'seznam srazů';	
		} else {
			$heading1 = 'Aktuální sraz';
			$heading2 = 'program';
		}

		$sql = "SELECT	*
				FROM kk_meetings
				WHERE id='".$mid."' AND deleted='0'
				LIMIT 1";
		$result = mysql_query($sql);
		$dbData = mysql_fetch_assoc($result);

		foreach($MeetingsHandler->form_names as $key) {
			$$key = requested($key, $dbData[$key]);
		}

		////inicializace promenych
		$error_start = "";
		$error_end = "";
		$error_open_reg = "";
		$error_close_reg = "";
		$error_login = "";

		// styles in header
		$style = CategoryModel::getStyles();

		if($cms == 'list-view') {
			$render = "<div class='link'><a class='link' href='process.php?cms=new&page=meetings'><img src='".IMG_DIR."icons/new.png' />NOVÝ SRAZ</a></div>\n";
			$render .= $MeetingsHandler->renderData();
		} else {
			$render = $MeetingsHandler->renderProgramOverview();
		}

		/* HTTP Header */
		$ViewHandler->loadTemplate('http_header');
		$ViewHandler->assign('config',		$GLOBALS['cfg']);
		$ViewHandler->assign('style',		$style);
		$ViewHandler->render(TRUE);

		/* Application Header */
		$ViewHandler->loadTemplate('header');
		$ViewHandler->assign('config',		$GLOBALS['cfg']);
		$ViewHandler->render(TRUE);

		// load and prepare template
		$ViewHandler->loadTemplate('meetings/meeting');
		$ViewHandler->assign('heading1',	$heading1);
		$ViewHandler->assign('heading2',	$heading2);
		$ViewHandler->assign('error',		printError($error));
		$ViewHandler->assign('cms',			$cms);
		$ViewHandler->assign('render',		$render);
		foreach($MeetingsHandler->form_names as $key) {
			//$$key = requested($key, $dbData[$key]);
			$ViewHandler->assign($key,	requested($key, $dbData[$key]));
		}
		$ViewHandler->assign('mid',			$mid);
		$ViewHandler->assign('error_start',			$error_start);
		$ViewHandler->assign('error_end',			$error_end);
		$ViewHandler->assign('error_open_reg',		$error_open_reg);
		$ViewHandler->assign('error_close_reg',		$error_close_reg);
		$ViewHandler->assign('error_login',			$error_login);
		$ViewHandler->render(TRUE);

		/* Footer */
		$ViewHandler->loadTemplate('footer');
		$ViewHandler->render(TRUE);
	}
}
