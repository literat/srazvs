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
	private $routing = '';

	/**
	 * database connection
	 * @var string
	 */
	private $database = NULL;

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
	public function init(array $getVars)
	{
		######################### PRISTUPOVA PRAVA ################################

		include_once(INC_DIR.'access.inc.php');

		###########################################################################

		$id = requested("id",$this->itemId);
		$this->cms = requested("cms","");
		$this->error = requested("error","");
		$this->page = requested("page","");


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

	public function setRouting($routing)
	{
		$this->routing = $routing;
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
	 * - ziska promenne z GET a POST
	 *
	 * @author tomasliterahotmail.com
	 *
	 * @param string $var - nazev pole GET nebo POST
	 * @param $default - defaultni hodnota v pripade neexistence GET nebo POST
	 */
	protected function requested($var, $default = NULL)
	{
		if(isset($_GET[$var])) $out = $this->clearString($_GET[$var]);
		elseif(isset($_POST[$var])) $out = $this->clearString($_POST[$var]);
		else $out = $default;

		return $out;
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
		//specialni znaky
		$string = htmlspecialchars($string);
		//html tagy
		$string = strip_tags($string);
		//slashes
		$string = stripslashes($string);
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

	protected function parseTutorEmail($item) {
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

}
