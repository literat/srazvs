<?php

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
}
