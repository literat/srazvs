<?php

namespace App\Presenters;

use Nette,
	App\Model;
use Nette\Utils\Strings;
use Nette\Http\Request;
use App\Models\SunlightModel;
use Nette\Caching\Cache;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{

	/**
	 * @var string
	 */
	public $backlink = '';

	/** @var Model */
	protected $model;

	/** @var Nette\DI\Container */
	protected $container;

	/** @var Latte */
	protected $latte;

	/** @var Router */
	protected $router;

	/** @var string */
	protected $action;

	/** @var Nette\Http\Request */
	protected $request;

	/** @var integer */
	protected $meetingId;

	protected $days = [
		'pátek'		=> 'pátek',
		'sobota'	=> 'sobota',
		'neděle'	=> 'neděle',
	];

	protected $hours = [
		0 => "00","01","02","03","04","05","06","07","08","09","10","11","12","13","14","15","16","17","18","19","20","21","22","23"
	];

	protected $minutes = [
		00 => "00",
		05 => "05",
		10 => "10",
		15 => "15",
		20 => "20",
		25 => "25",
		30 => "30",
		35 => "35",
		40 => "40",
		45 => "45",
		50 => "50",
		55 => "55",
	];

	/**
	 * Startup
	 */
	protected function startup()
	{
		parent::startup();

		$meetingId = $this->getRequest()->getQuery('mid', '');

		$backlink = $this->getRequest()->getQuery('backlink');
		if(!empty($backlink)) {
			$this->setBacklink($backlink);
		}

		if($meetingId){
			$_SESSION['meetingID'] = $meetingId;
		} elseif(!isset($_SESSION['meetingID'])) {
			$meeting = $this->getContainer()->getService('meeting');
			$_SESSION['meetingID'] = $meeting->getLastMeetingId();
		}

		$this->setMeetingId($_SESSION['meetingID']);

		$model = $this->getModel();
		if($model) {
			$model->setMeetingId($_SESSION['meetingID']);
		}

		$this->debugMode = $this->getContainer()->getParameters()['debugMode'];
	}


	/**
	 * Before render
	 * Prepare variables for template
	 */
	public function beforeRender()
	{
		parent::beforeRender();

		$template = $this->getTemplate();
		$meeting = $this->getContainer()->getService('meeting');

		$template->baseDir = ROOT_DIR;
		$template->wwwDir = HTTP_DIR;
		$template->cssDir = CSS_DIR;
		$template->jsDir = JS_DIR;
		$template->imgDir = IMG_DIR;
		$template->catDir = CAT_DIR;
		$template->blockDir = BLOCK_DIR;
		$template->progDir = PROG_DIR;
		$template->visitDir = VISIT_DIR;
		$template->expDir = EXP_DIR;

		$template->categories = $this->remember('categories:all', 10, function () {
			return $this->getContainer()->getService('category')->all();
		});

		if(isset($_SESSION[SESSION_PREFIX.'user'])) {
			$template->user = $this->getSunlight()->findUser($_SESSION[SESSION_PREFIX.'user']);
		}
		$template->meeting = $meeting->getPlaceAndYear($_SESSION['meetingID']);
		$template->menuItems = $meeting->getMenuItems();
		$template->meeting_heading	= $meeting->getRegHeading();
		$template->meetingId = $this->getMeetingId();
		$template->backlinkUrl = $this->getBacklinkUrl();
		$template->backlink = $this->getBacklink();
		//$this->template->backlink = $this->getParameter("backlink");

		//$this->template->production = $this->context->parameters['environment'] === 'production' ? 1 : 0;
		//$this->template->version = $this->context->parameters['site']['version'];
	}

	/**
	 * template
	 * @var string
	 */
	protected $template = 'listing';

	/**
	 * template directory
	 * @var string
	 */
	protected $templateDir = '';

	/**
	 * category ID
	 * @var integer
	 */
	protected $itemId = NULL;

	/**
	 * action what to do
	 * @var string
	 */
	protected $cms = '';

	/**
	 * page where to return
	 * @var string
	 */
	protected $page = '';

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
	protected $data = array();

	/**
	 * error handler
	 * @var string
	 */
	protected $error = '';

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
	 * @return Model
	 */
	protected function getModel()
	{
		return $this->model;
	}

	/**
	 * @param  Model $model
	 * @return $this
	 */
	protected function setModel($model)
	{
		$this->model = $model;
		return $this;
	}

	/**
	 * @return Container
	 */
	protected function getContainer()
	{
		return $this->context;
	}

	/**
	 * @param  Container $container
	 * @return $this
	 */
	protected function setContainer($container)
	{
		$this->context = $container;
		return $this;
	}

	/**
	 * @return Router
	 */
	protected function getRouter()
	{
		return $this->router;
	}

	/**
	 * @param  Router $router
	 * @return $this
	 */
	protected function setRouter($router)
	{
		$this->router = $router;
		return $this;
	}

	/**
	 * @return Latte
	 */
	protected function getLatte()
	{
		return $this->latte;
	}

	/**
	 * @param  Latte $latte
	 * @return $this
	 */
	protected function setLatte($latte)
	{
		$this->latte = $latte;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getAction($fullyQualified = false)
	{
		return $this->action;
	}

	/**
	 * @param  string $action
	 * @return $this
	 */
	public function setAction($action)
	{
		$this->action = $action;
		return $this;
	}

	/**
	 * @return SunlightModel
	 */
	public function getSunlight()
	{
		if(empty($this->sunlight)) {
			$this->setSunlight($this->getContainer()->getService('sunlight'));
		}

		return $this->sunlight;
	}

	/**
	 * @param  SunlightModel $sunlight
	 * @return $this
	 */
	public function setSunlight(SunlightModel $sunlight)
	{
		$this->sunlight = $sunlight;
		return $this;
	}

	/**
	 * @return Nette\Http\Request
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * @param  Request $request
	 * @return $this
	 */
	public function setRequest(Request $request)
	{
		$this->request = $request;
		return $this;
	}

	/**
	 * @return integer
	 */
	protected function getMeetingId()
	{
		return $this->meetingId;
	}

	/**
	 * @param  integer  $meetingId
	 * @return $this
	 */
	protected function setMeetingId($meetingId)
	{
		$this->meetingId = $meetingId;
		return $this;
	}

	/**
	 * @param  string $guid
	 * @param  array  $data
	 * @return ActiveRow
	 */
	protected function updateByGuid($guid, array $data)
	{
		return $this->getModel()->updateBy('guid', $guid, $data);
	}

	public function remember($key, $minutes, \Closure $callback)
	{
		// If the item exists in the cache we will just return this immediately
		// otherwise we will execute the given Closure and cache the result
		// of that execution for the given number of minutes in storage.
		if (! is_null($data = $this->getCache()->load($key))) {
			$items = [];

			foreach($data as $item) {
				$object = new \stdClass();
				foreach ($item as $key => $value) {
					$object->$key = $value;
				}
				$items[] = $object;
			}

			return $items;
		}

		$data = $callback();
		$serialized = [];
		foreach ($data as $item) {
			$serialized[] = $item->toArray();
		}

		$this->getCache()->save(
			$key,
			$serialized,
			[
				Cache::EXPIRE => $minutes . ' minutes',
			]
		);

		return $data;
	}

	protected function getCache()
	{
		return $this->getContainer()->getService('cache');
	}

	/**
	 * @return string
	 */
	protected function getDebugMode()
	{
		return $this->debugMode;
	}

	/**
	 * @return string
	 */
	protected function getBacklink()
	{
		return $this->backlink;
	}

	/**
	 * @param  string $backlink
	 * @return $this
	 */
	protected function setBacklink($backlink)
	{
		$this->backlink = $backlink;

		return $this;
	}

	/**
	 * @return string
	 */
	protected function getBacklinkUrl()
	{
		if($this->getBacklink()) {
			return $this->link(
				$this->getBacklink(),
				[
					'backlink' => null
				]
			);
		}
	}

}
