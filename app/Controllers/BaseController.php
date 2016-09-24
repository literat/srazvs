<?php

use Nette\Utils\Strings;

/**
 * Base Controller
 *
 * abstract base class for all controllers
 *
 * @author Tomas Litera
 * @copyright 2013-06-12 <tomaslitera@hotmail.com>
 * @package srazvs
 */
abstract class BaseController
{
	/**
	 * template
	 * @var string
	 */
	private $template = 'listing';
	private $latte;

	/**
	 * template directory
	 * @var string
	 */
	private $templateDir = '';

	/**
	 * meeting ID
	 * @var integer
	 */
	private $meetingId = 0;

	/**
	 * category ID
	 * @var integer
	 */
	protected $itemId = NULL;

	/**
	 * action what to do
	 * @var string
	 */
	private $cms = '';

	/**
	 * page where to return
	 * @var string
	 */
	private $page = '';

	/**
	 * heading tetxt
	 * @var string
	 */
	protected $heading = '';

	/**
	 * action what to do next
	 * @var string
	 */
	protected $todo = '';

	/**
	 * data
	 * @var array
	 */
	private $data = array();

	/**
	 * error handler
	 * @var string
	 */
	private $error = '';

	/**
	 * error handler
	 * @var string
	 */
	protected $router = '';

	/**
	 * database connection
	 * @var string
	 */
	protected $database = NULL;

	/**
	 * debug mode
	 * @var boolean
	 */
	protected $debugMode = false;

	/**
	 * This is the default function that will be called by Router.php
	 *
	 * @param array $getVars the GET variables posted to index.php
	 */
	public function init()
	{
		######################### PRISTUPOVA PRAVA ################################

		include_once(INC_DIR.'access.inc.php');

		###########################################################################

		$id = $this->requested("id",$this->itemId);
		$this->cms = $this->requested("cms","");
		$this->error = $this->requested("error","");
		$this->page = $this->requested("page","");


		switch($this->cms) {
			case "delete":
				$this->delete($id);
				break;
			case "new":
				$this->__new();
				break;
			case "create":
				$this->create();
				break;
			case "edit":
				$this->edit($id);
				break;
			case "modify":
				$this->update($id);
				break;
			case "mail":
				$this->mail();
				break;
			case 'export-visitors':
				$this->Export->printProgramVisitors($id);
				break;

		}

		$this->render();
	}

	public function setRouter($router)
	{
		$this->router = $router;
	}

	protected function code4Bank($data)
	{
		return Strings::toAscii(
			mb_substr($data['name'], 0, 1, 'utf-8')
			. mb_substr($data['surname'], 0, 1, 'utf-8')
			. mb_substr($data['birthday'], 2, 2)
		);
	}

	/**
	 * requested()
	 * - ziska promenne z GET
	 *
	 * @author tomasliterahotmail.com
	 *
	 * @param string $var - nazev pole GET
	 * @param $default - defaultni hodnota v pripade neexistence GET
	 */
	protected function requested($var, $default = NULL)
	{
		if($this->router->getParameter($var)) $out = $this->clearString($this->router->getParameter($var));
		elseif($this->router->getPost($var)) $out = $this->clearString($this->router->getPost($var));
		else $out = $default;

		return $out;
	}

	protected function processClearString($string)
	{
		//specialni znaky
		$string = htmlspecialchars($string);
		//html tagy
		$string = strip_tags($string);
		//slashes
		$string = stripslashes($string);

		return $string;
	}

	/**
	 * clearString()
	 * - ocisti retezec od html, backslashu a specialnich znaku
	 *
	 * @author tomas.litera@gmail.com
	 *
	 * @param string $string - retezec znaku
	 * @return string $string - ocisteny retezec
	 */
	protected function clearString($string)
	{
		if(is_array($string)) {
			foreach ($string as $key => $value) {
				$string[$key] = $this->processClearString($value);
			}
		} else {
			$string = $this->processClearString($string);
		}
		return $string;
	}

	/**
	 * Render check box
	 *
	 * @param	string	name
	 * @param	mixed	value
	 * @param	var		variable that match with value
	 * @return	string	html of chceck box
	 */
	protected function renderHtmlCheckBox($name, $value, $checked_variable)
	{
		if($checked_variable == $value) {
			$checked = 'checked';
		} else {
			$checked = '';
		}
		$html_checkbox = "<input name='".$name."' type='checkbox' value='".$value."' ".$checked." />";

		return $html_checkbox;
	}

	protected function parseTutorEmail($item)
	{
		$mails = explode(',', $item->email);
		$names = explode(',', $item->tutor);

		$i = 0;
		foreach ($mails as $mail) {
			$mail = trim($mail);
			$name = trim($names[$i]);

			$recipient[$mail] = ($name) ? $name : '';
		}

		return $recipient;
	}

	// zaheshovane udaje, aby se nedali jen tak ziskat data z databaze
	protected function formKeyHash($id, $meetingId)
	{
		return ((int)$id . $meetingId) * 116 + 39147;
	}

	/**
	 *
	 *
	 */
	protected function getUser($uid)
	{
		return $this->database
			->table('sunlight-users')
			->where('id', $uid)
			->fetch();
	}

	protected function generateMenu()
	{
		$menuItems = $this->database->query(
			'SELECT id AS mid,
					place,
					DATE_FORMAT(start_date, "%Y") AS year
			FROM kk_meetings
			WHERE deleted = ?
			ORDER BY id DESC',
			'0')->fetchAll();

		$menu = "<!-- start of menuCanvas -->\n";
		$menu .= "<div id='menuCanvas'>\n";
		$menu .= " <div id='menuContent'>\n";

		$menu .= "  <div class='menuItem'>všechny srazy</div>\n";
		$menu .= "   <ul>";
		$menu .= "    <li><a href='".MEET_DIR."/?cms=list-view'>seznam srazů</a></li>\n";
		$menu .= "   </ul>";

		$menu .= "  <div class='menuItem'>jednotlivé srazy</div>\n";
		$menu .= "   <ul>";

		foreach($menuItems as $item) {
			$menu .= "    <li><a href='?mid=".$item['mid']."'>".$item['place']." ".$item['year']."</a></li>\n";
		}

		$menu .= "   </ul>";
		$menu .= " </div>\n";
		$menu .= "</div>\n";
		$menu .= "<!-- end of menuCanvas -->\n";

		return $menu;
	}

	protected function getPlaceAndYear($meetingId)
	{
		return $this->database->query(
			'SELECT	place, DATE_FORMAT(start_date, "%Y") AS year
			FROM kk_meetings
			WHERE id = ? AND deleted = ?
			LIMIT 1', $meetingId, '0')
			->fetch();
	}
}
