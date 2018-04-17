<?php

namespace App\Presenters;

use App\Components\INavbarRightControlFactory;
use App\Components\NavbarRightControl;
use App\Models\BaseModel;
use App\Traits\Loggable;
use Nette;
use Nette\Caching\Cache;
use Nette\Database\Table\ActiveRow;
use Nette\DI\Container;
use Nette\Utils\ArrayHash;

abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	use Loggable;

	const FLASH_TYPE_OK    = 'success';
	const FLASH_TYPE_ERROR = 'error';
	const ROLE_ADMIN = 'admin';
	const ROLE_GUEST = 'guest';

	/**
	 * @var string
	 */
	public $backlink = '';

	/**
	 * @var \App\Models\BaseModel
	 */
	protected $model;

	/**
	 * @var \Nette\DI\Container
	 */
	protected $container;

	/**
	 * @var string
	 */
	protected $action;

	/**
	 * @var \Nette\Http\Request
	 */
	protected $request;

	/**
	 * @var integer
	 */
	protected $meetingId;

	/**
	 * @var INavbarRightControlFactory
	 */
	protected $navbarRightControlFactory;

	/**
	 * @var string
	 */
	protected $template = 'listing';

	/**
	 * @var string
	 */
	protected $templateDir = '';

	/**
	 * @var integer
	 */
	protected $itemId = null;

	/**
	 * @var string
	 */
	protected $cms = '';

	/**
	 * @var string
	 */
	protected $page = '';

	/**
	 * @var string
	 */
	protected $heading = '';

	/**
	 * @var string
	 */
	protected $todo = '';

	/**
	 * @var array
	 */
	protected $data = [];

	/**
	 * @var string
	 */
	protected $error = '';

	/**
	 * @var string
	 */
	protected $database = null;

	/**
	 * @var boolean
	 */
	protected $debugMode = false;

	public function injectNavbarRightControlFactory(INavbarRightControlFactory $factory): self
	{
		$this->navbarRightControlFactory = $factory;

		return $this;
	}

	protected function startup()
	{
		parent::startup();

		$meetingId = $this->getHttpRequest()->getQuery('mid', '');

		$backlink = $this->getHttpRequest()->getQuery('backlink');
		if (!empty($backlink)) {
			$this->setBacklink($backlink);
		}

		$meetingSession = $this->getSession('meeting');
		if ($meetingId) {
			$meetingSession->meetingId = $meetingId;
		//$_SESSION['meetingID'] = $meetingId;
		} elseif ($meetingSession->meetingId/*!isset($_SESSION['meetingID'])*/) {
			$meeting = $this->getContainer()->getService('meeting');
			//$_SESSION['meetingID'] = $meeting->getLastMeetingId();
			$meetingSession->meetingId = $meeting->getLastMeetingId();
		}

		$this->setMeetingId($meetingSession->meetingId);
		//$this->setMeetingId($_SESSION['meetingID']);

		$model = $this->getModel();
		if ($model) {
			//$model->setMeetingId($_SESSION['meetingID']);
			$model->setMeetingId($meetingSession->meetingId);
		}

		$this->debugMode = $this->getContainer()->getParameters()['debugMode'];
	}


	/**
	 * Prepare variables for template.
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
		$template->meetDir = MEET_DIR;

		$template->categories = $this->remember('categories:all', 10, function () {
			return $this->getContainer()->getService('category')->all();
		});

		//$template->meeting = $meeting->getPlaceAndYear($_SESSION['meetingID']);
		$template->meeting = $meeting->getPlaceAndYear($this->getSession('meeting')->meetingId);
		$template->menuItems = $meeting->getMenuItems();
		$template->meeting_heading	= $meeting->getRegHeading();
		$template->meetingId = $this->getMeetingId();
		//$template->backlinkUrl = $this->getBacklinkUrl();
		$template->backlink = $this->getBacklink();

		//$this->template->production = $this->context->parameters['environment'] === 'production' ? 1 : 0;
		//$this->template->version = $this->context->parameters['site']['version'];
	}

	protected function parseTutorEmail($item)
	{
		$mails = explode(',', $item->email);
		$names = explode(',', $item->tutor);

		$i = 0;
		$recipient = [];
		foreach ($mails as $mail) {
			$mail = trim($mail);
			$name = trim($names[$i]);

			$recipient['email'] = $mail;
			$recipient['name'] = ($name) ? $name : '';
			$recipients[] = ArrayHash::from($recipient);
		}

		return $recipients;
	}

	// zaheshovane udaje, aby se nedali jen tak ziskat data z databaze
	protected function formKeyHash($id, $meetingId)
	{
		return ((int) $id . $meetingId) * 116 + 39147;
	}

	protected function getModel(): BaseModel
	{
		return $this->model;
	}

	protected function setModel(BaseModel $model): self
	{
		$this->model = $model;

		return $this;
	}

	protected function getContainer(): Container
	{
		return $this->context;
	}

	// @codingStandardsIgnoreStart
	public function getAction($fullyQualified = false): string
	{
		return $this->action;
	}
	// @codingStandardsIgnoreEnd

	/**
	 * @param  string $action
	 * @return self
	 */
	public function setAction($action): self
	{
		$this->action = $action;

		return $this;
	}

	/**
	 * @return integer
	 */
	protected function getMeetingId(): int
	{
		return $this->meetingId;
	}

	/**
	 * @param  integer $meetingId
	 * @return self
	 */
	protected function setMeetingId($meetingId): self
	{
		$this->meetingId = $meetingId;

		return $this;
	}

	protected function updateByGuid(string $guid, array $data): ActiveRow
	{
		return $this->getModel()->updateBy('guid', $guid, $data);
	}

	public function remember($key, $minutes, \Closure $callback)
	{
		// If the item exists in the cache we will just return this immediately
		// otherwise we will execute the given Closure and cache the result
		// of that execution for the given number of minutes in storage.
		if (($data = $this->getCache()->load($key)) !== null) {
			$items = [];

			foreach ($data as $item) {
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

	protected function createComponentNavbarRight(): NavbarRightControl
	{
		return $this->navbarRightControlFactory->create();
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
	 * @return self
	 */
	protected function setBacklink($backlink): self
	{
		$this->backlink = $backlink;

		return $this;
	}

	/**
	 * @return string
	 * @throws Nette\Application\UI\InvalidLinkException
	 */
	protected function getBacklinkUrl()
	{
		if ($this->getBacklink()) {
			return $this->link(
				$this->getBacklink(),
				[
					'backlink' => null
				]
			);
		}
	}

	/**
	 * @param  Nette\Utils\ArrayHash $array
	 * @return self
	 */
	protected function setBacklinkFromArray(ArrayHash $array): self
	{
		if (array_key_exists('backlink', $array) && !empty($array['backlink'])) {
			$this->setBacklink($array['backlink']);
			unset($array['backlink']);
		}

		return $this;
	}

	/**
	 * Flashes success message.
	 *
	 * @param string $message Message
	 */
	protected function flashSuccess(string $message = '')
	{
		$this->flashMessage($message, self::FLASH_TYPE_OK);
	}

	/**
	 * Flashes failure message.
	 *
	 * @param string $message Message
	 */
	protected function flashFailure(string $message = '')
	{
		$this->flashMessage($message, self::FLASH_TYPE_ERROR);
	}

	/**
	 * Flashes error message.
	 *
	 * @param string $message Message
	 */
	protected function flashError(string $message = '')
	{
		$this->flashMessage($message, self::FLASH_TYPE_ERROR);
	}

	/**
	 * @throws Nette\Application\BadRequestException
	 */
	protected function unauthorized()
	{
		$message = 'Nemáte oprávnění pro tuto stránku. Prosím, přihlašte se nebo požádejte administrátora.';
		$this->flashFailure($message);
		$this->error($message, Nette\Http\IResponse::S403_FORBIDDEN);
	}

	/**
	 * @throws Nette\Application\BadRequestException
	 */
	protected function allowAdminAccessOnly()
	{
		$user = $this->getUser();
		if (!$user->isInRole(self::ROLE_ADMIN)) {
			$this->unauthorized();
		}
	}
}
