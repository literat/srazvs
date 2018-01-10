<?php

namespace App\Presenters;

use App\Services\Emailer;
use App\Repositories\BlockRepository;
use App\Models\MeetingModel;
use App\Components\Forms\Factories\IBlockFormFactory;
use App\Components\Forms\BlockForm;
use \Exception;

/**
 * Block controller
 *
 * This file handles the retrieval and serving of blocks
 *
 * @created 2013-06-03
 * @author Tomas Litera <tomaslitera@hotmail.com>
 */
class BlockPresenter extends BasePresenter
{

	const REDIRECT_DEFAULT = 'Block:listing';

	/**
	 * @var integer
	 */
	private $blockId = NULL;

	/**
	 * @var Emailer
	 */
	private $emailer;

	/**
	 * @var MeetingModel
	 */
	private $meetingModel;

	/**
	 * @var BlockRepository
	 */
	private $blockRepository;

	/**
	 * @var IBlockFormFactory
	 */
	private $blockFormFactory;

	/**
	 * @param BlockRepository $blockRepository
	 * @param Emailer         $emailer
	 */
	public function __construct(BlockRepository $blockRepository, Emailer $emailer, MeetingModel $meetingModel)
	{
		$this->setBlockRepository($blockRepository);
		$this->setEmailer($emailer);
		$this->setMeetingModel($meetingModel);
	}

	/**
	 * @param  IBlockFormFactory $factory
	 */
	public function injectBlockFormFactory(IBlockFormFactory $factory)
	{
		$this->blockFormFactory = $factory;
	}

	/**
	 * @return void
	 */
	public function startup()
	{
		parent::startup();

		$meetingId = $this->getMeetingId();
		$this->getBlockRepository()->setMeetingId($meetingId);
	}

	/**
	 * @return void
	 */
	public function actionCreate()
	{
		$model = $this->getModel();
		$data = $this->getHttpRequest()->getPost();

		$this->setBacklink($data['backlink']);
		$data['from'] = date('H:i:s', mktime($data['start_hour'], $data['start_minute'], 0, 0, 0, 0));
		$data['to'] = date('H:i:s', mktime($data['end_hour'], $data['end_minute'], 0, 0, 0, 0));
		$data['meeting'] = $this->getMeetingId();

		unset($data['start_hour']);
		unset($data['end_hour']);
		unset($data['start_minute']);
		unset($data['end_minute']);
		unset($data['backlink']);

		try {
			$this->guardToGreaterThanFrom($data['from'], $data['to']);
			$result = $this->getModel()->create($data);

			$this->logInfo('Creation of block successfull, result: ' . json_encode($result));

			$this->flashSuccess('Položka byla úspěšně vytvořena');
		} catch(Exception $e) {
			$this->logError('Creation of block with data ' . json_encode($data) . ' failed, result: ' . $e->getMessage());

			$this->flashFailure('Creation of block failed, result: ' . $e->getMessage());
		}

		$this->redirect($this->getBacklink() ?: self::REDIRECT_DEFAULT);
	}

	/**
	 * @param  integer $id
	 * @return void
	 */
	public function actionUpdate($id)
	{
		$model = $this->getModel();
		$data = $this->getHttpRequest()->getPost();

		$this->setBacklink($data['backlink']);
		$data['from'] = date('H:i:s', mktime($data['start_hour'], $data['start_minute'], 0, 0, 0, 0));
		$data['to'] = date('H:i:s', mktime($data['end_hour'], $data['end_minute'], 0, 0, 0, 0));
		$data['meeting'] = $this->getMeetingId();
		array_key_exists('display_progs', $data) ?: $data['display_progs'] = '1';

		unset($data['start_hour']);
		unset($data['end_hour']);
		unset($data['start_minute']);
		unset($data['end_minute']);
		unset($data['backlink']);

		try {
			$this->guardToGreaterThanFrom($data['from'], $data['to']);
			$result = $this->getModel()->update($id, $data);

			$this->logInfo('Modification of block id ' . $id . ' with data ' . json_encode($data) . ' successfull, result: ' . json_encode($result));

			$this->flashSuccess('Položka byla úspěšně upravena.');
		} catch(Exception $e) {
			$this->logError('Modification of block id ' . $id . ' failed, result: ' . $e->getMessage());

			$this->flashFailure('Modification of block id ' . $id . ' failed, result: ' . $e->getMessage());
		}

		$this->redirect($this->getBacklink() ?: self::REDIRECT_DEFAULT);
	}

	/**
	 * @param  int  $id
	 * @return void
	 */
	public function actionDelete($id): void
	{
		try {
			$result = $this->getModel()->delete($id);
			$this->logInfo('Destroying of block successfull, result: ' . json_encode($result));
			$this->flashSuccess('Položka byla úspěšně smazána.');
		} catch(Exception $e) {
			$this->logError('Destroying of block failed, result: ' .  $e->getMessage());
			$this->flashFailure('Destroying of block failed, result: ' . $e->getMessage());
		}

		$this->redirect(self::REDIRECT_DEFAULT);
	}

	/**
	 * Send mail to tutor
	 *
	 * @return void
	 */
	public function actionMail($id)
	{
		try {
			$tutors = $this->getModel()->getTutor($id);
			$recipients = $this->parseTutorEmail($tutors);

			$this->getEmailer()->tutor($recipients, $tutors->guid, 'block');

			$this->logInfo('Sending email to block tutor successfull, result: ' . json_encode($recipients) . ', ' . $tutors->guid);
			$this->flashSuccess('Email lektorovi byl odeslán..');
		} catch(Exception $e) {
			$this->logError('Sending email to block tutor failed, result: ' .  $e->getMessage());
			$this->flashFailure('Email lektorovi nebyl odeslán, result: ' . $e->getMessage());
		}

		$this->redirect('Block:edit', $id);
	}

	/**
	 * @return void
	 */
	public function renderNew()
	{
		$template = $this->getTemplate();

		$template->heading = 'nový blok';
		$template->page = $this->getHttpRequest()->getQuery('page');
		$template->error_name = "";
		$template->error_description = "";
		$template->error_tutor = "";
		$template->error_email = "";
		$template->error_material = "";

		$template->day_roll = $this->renderHtmlSelectBox('day', $this->days, null, 'width:172px;');
		$template->hour_roll = $this->renderHtmlSelectBox('start_hour', $this->hours, date('H'));
		$template->minute_roll = $this->renderHtmlSelectBox('start_minute', $this->minutes, date('i'));
		$template->end_hour_roll = $this->renderHtmlSelectBox('end_hour', $this->hours, date('H')+1);
		$template->end_minute_roll = $this->renderHtmlSelectBox('end_minute', $this->minutes, date('i'));
		// is program block check box
		$template->program_checkbox = $this->renderHtmlCheckBox('program', 1, 0);
		// display programs in block check box
		$template->display_progs_checkbox = $this->renderHtmlCheckBox('display_progs', 0, null);
		$template->selectedCategory	= null;
	}

	/**
	 * Prepare data for editing
	 *
	 * @param  int $id of Block
	 * @return void
	 */
	public function renderEdit($id)
	{
		$template = $this->getTemplate();

		$template->heading = 'úprava bloku';
		$template->page = $this->getHttpRequest()->getQuery('page');
		$template->error_name = "";
		$template->error_description = "";
		$template->error_tutor = "";
		$template->error_email = "";
		$template->error_material = "";

		$this->blockId = $id;
		$block = $this->getBlockRepository()->find($id);
		$template->block = $block;
		$template->id = $id;

		$template->day_roll = $this->renderHtmlSelectBox('day', $this->days, $block->day, 'width:172px;');
		$template->hour_roll = $this->renderHtmlSelectBox('start_hour', $this->hours, $block->from->format('%H'));
		$template->minute_roll = $this->renderHtmlSelectBox('start_minute', $this->minutes, $block->from->format('%I'));
		$template->end_hour_roll = $this->renderHtmlSelectBox('end_hour', $this->hours, $block->to->format('%H'));
		$template->end_minute_roll = $this->renderHtmlSelectBox('end_minute', $this->minutes, $block->to->format('%I'));
		// is program block check box
		$template->program_checkbox = $this->renderHtmlCheckBox('program', 1, $block->program);
		// display programs in block check box
		$template->display_progs_checkbox = $this->renderHtmlCheckBox('display_progs', 0, $block->display_progs);
		$template->selectedCategory	= $block->category;
	}

	/**
	 * @return void
	 */
	public function renderListing()
	{
		$model = $this->getModel();
		$template = $this->getTemplate();

		$template->blocks = $this->getBlockRepository()->all();
		$template->mid = $this->meetingId;
		$template->heading = $this->heading;
	}

	/**
	 * @return BlockForm
	 */
	protected function createComponentBlockForm(): BlockForm
	{
		$control = $this->blockFormFactory->create();
		$control->setMeetingId($this->getMeetingId());
		$control->onBlockSave[] = function(BlockForm $control, $block) {
			//$guid = $this->getParameter('guid');
			$id = $this->getParameter('id');

			$this->setBacklinkFromArray($block);

			if($id) {
				$this->actionUpdate($id, $block);
			} else {
				$this->actionCreate($block);
			}

			$this->redirect($this->getBacklink() ?: self::REDIRECT_DEFAULT);
		};

		$control->onBlockReset[] = function(BlockForm $control, $block) {
			$this->setBacklinkFromArray($block);

			$this->redirect($this->getBacklink() ?: self::REDIRECT_DEFAULT);
		};

		return $control;
	}

	/**
	 * @return Emailer
	 */
	protected function getEmailer()
	{
		return $this->emailer;
	}

	/**
	 * @param  Emailer $emailer
	 * @return $this
	 */
	protected function setEmailer(Emailer $emailer)
	{
		$this->emailer = $emailer;
		return $this;
	}

	/**
	 * Render select box
	 *
	 * @param	string	name
	 * @param	array	content of select box
	 * @param	var		variable that match selected option
	 * @param	string	inline styling
	 * @return	string	html of select box
	 */
	private function renderHtmlSelectBox($name, $select_content, $selected_option, $inline_style = NULL)
	{
		if(isset($inline_style) && $inline_style != NULL){
			$style = " style='".$inline_style."'";
		} else {
			$style = "";
		}
		$html_select = "<select name='".$name."'".$style.">";
		foreach ($select_content as $key => $value) {
			if($key == $selected_option) {
				$selected = 'selected';
			} else {
				$selected = '';
			}
			$html_select .= "<option value='".$key."' ".$selected.">".$value."</option>";
		}
		$html_select .= '</select>';

		return $html_select;
	}

	/**
	 * @param  date $from
	 * @param  date $to
	 * @return Exception
	 */
	private function guardToGreaterThanFrom($from, $to)
	{
		if($from > $to) {
			throw new Exception('Starting time is greater then finishing time.');
		}
	}


	/**
	 * @return MeetingModel
	 */
	private function getMeetingModel(): MeetingModel
	{
		return $this->meetingModel;
	}

	/**
	 * @param MeetingModel $meetingModel
	 *
	 * @return self
	 */
	private function setMeetingModel(MeetingModel $meetingModel): self
	{
		$this->meetingModel = $meetingModel;

		return $this;
	}

	/**
	 * @return BlockRepository
	 */
	private function getBlockRepository(): BlockRepository
	{
		return $this->blockRepository;
	}

	/**
	 * @param BlockRepository $blockRepository
	 */
	private function setBlockRepository(BlockRepository $blockRepository): self
	{
		$this->blockRepository = $blockRepository;

		return $this;
	}



}
