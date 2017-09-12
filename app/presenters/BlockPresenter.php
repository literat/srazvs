<?php

namespace App\Presenters;

use Nette\Database\Context;
use Tracy\Debugger;
use App\Services\Emailer;
use App\Models\BlockModel;
use App\Models\MeetingModel;
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
	 * @param BlockModel $model
	 * @param Emailer    $emailer
	 */
	public function __construct(BlockModel $model, Emailer $emailer, MeetingModel $meetingModel)
	{
		$this->setModel($model);
		$this->setEmailer($emailer);
		$this->setMeetingModel($meetingModel);
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

			Debugger::log('Creation of block successfull, result: ' . json_encode($result), Debugger::INFO);

			$this->flashMessage('Položka byla úspěšně vytvořena', 'ok');
		} catch(Exception $e) {
			Debugger::log('Creation of block with data ' . json_encode($data) . ' failed, result: ' . $e->getMessage(), Debugger::ERROR);

			$this->flashMessage('Creation of block failed, result: ' . $e->getMessage(), 'error');
		}

		$this->redirect($this->getBacklink() ?: 'Block:listing');
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

			Debugger::log('Modification of block id ' . $id . ' with data ' . json_encode($data) . ' successfull, result: ' . json_encode($result), Debugger::INFO);

			$this->flashMessage('Položka byla úspěšně upravena.', 'ok');
		} catch(Exception $e) {
			Debugger::log('Modification of block id ' . $id . ' failed, result: ' . $e->getMessage(), Debugger::ERROR);

			$this->flashMessage('Modification of block id ' . $id . ' failed, result: ' . $e->getMessage(), 'error');
		}

		$this->redirect($this->getBacklink() ?: 'Block:listing');
	}

	/**
	 * @param  integer $id
	 * @return void
	 */
	public function actionAnnotationupdate($id)
	{
		try {
			$data = $this->getHttpRequest()->getPost();
			$result = $this->updateByGuid($id, $data);

			Debugger::log('Modification of block annotation id ' . $id . ' with data ' . json_encode($data) . ' successfull, result: ' . json_encode($result), Debugger::INFO);

			$this->flashMessage('Položka byla úspěšně upravena.', 'ok');
		} catch(Exception $e) {
			Debugger::log('Modification of block annotation guid ' . $id . ' failed, result: ' . $e->getMessage(), Debugger::ERROR);

			$this->flashMessage('Modification of block annotation guid ' . $id . ' failed, result: ' . $e->getMessage(), 'error');
		}

		$this->redirect('Block:annotation', $id);
	}

	/**
	 * @param  int  $id
	 * @return void
	 */
	public function actionDelete($id)
	{
		try {
			$result = $this->getModel()->delete($id);
			Debugger::log('Destroying of block successfull, result: ' . json_encode($result), Debugger::INFO);
			$this->flashMessage('Položka byla úspěšně smazána.', 'ok');
		} catch(Exception $e) {
			Debugger::log('Destroying of block failed, result: ' .  $e->getMessage(), Debugger::ERROR);
			$this->flashMessage('Destroying of block failed, result: ' . $e->getMessage(), 'error');
		}

		$this->redirect('Block:listing');
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

			Debugger::log('Sending email to block tutor successfull, result: ' . json_encode($recipients) . ', ' . $tutors->guid, Debugger::INFO);
			$this->flashMessage('Email lektorovi byl odeslán..', 'ok');
		} catch(Exception $e) {
			Debugger::log('Sending email to block tutor failed, result: ' .  $e->getMessage(), Debugger::ERROR);
			$this->flashMessage('Email lektorovi nebyl odeslán, result: ' . $e->getMessage(), 'error');
		}

		$this->redirect('Block:edit', $id);
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
		$block = $this->getModel()->find($id);
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
	 * Prepare data for annotation
	 *
	 * @param  int $id of item
	 * @return void
	 */
	public function renderAnnotation($id)
	{
		$template = $this->getTemplate();

		$template->page_title = 'Registrace programů pro lektory';
		$template->page = $this->getHttpRequest()->getQuery('page');
		$template->error_name = "";
		$template->error_description = "";
		$template->error_tutor = "";
		$template->error_email = "";
		$template->error_material = "";

		$block = $this->getModel()->findBy('guid', $id);
		$meeting = $this->getMeetingModel()->find($this->getMeetingId());

		$this->blockId = $block->id;
		$template->block = $block;
		$template->meeting = $meeting;
		$template->id = $id;
	}

	/**
	 * @return void
	 */
	public function renderListing()
	{
		$model = $this->getModel();
		$template = $this->getTemplate();

		$template->blocks = $model->all();
		$template->mid = $this->meetingId;
		$template->heading = $this->heading;
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
	 * @param	array	content of slect box
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
	public function getMeetingModel(): MeetingModel
	{
		return $this->meetingModel;
	}

	/**
	 * @param MeetingModel $meetingModel
	 *
	 * @return self
	 */
	public function setMeetingModel(MeetingModel $meetingModel): self
	{
		$this->meetingModel = $meetingModel;

		return $this;
	}

}
