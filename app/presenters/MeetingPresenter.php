<?php

namespace App\Presenters;

use App\Models\MeetingModel;
use Tracy\Debugger;
use Exception;

/**
 * This file handles the retrieval and serving of news articles
 */
class MeetingPresenter extends BasePresenter
{

	/**
	 * Prepare initial values
	 */
	public function __construct(MeetingModel $model)
	{
		$this->setModel($model);
	}

	/**
	 * @throws \Nette\Application\AbortException
	 */
	public function startup()
	{
		parent::startup();
		$this->allowAdminAccessOnly();
	}

	/**
	 * @return void
	 */
	public function actionCreate()
	{
		try {
			$model = $this->getModel();
			$data = $this->getHttpRequest()->getPost();
			$result = $this->getModel()->create($data);

			Debugger::log('Creation of meeting successfull, result: ' . json_encode($result), Debugger::INFO);
			$this->flashMessage('Položka byla úspěšně vytvořena', 'ok');
		} catch(Exception $e) {
			Debugger::log('Creation of meeting with data ' . json_encode($data) . ' failed, result: ' . $e->getMessage(), Debugger::ERROR);
			$this->flashMessage('Creation of meeting failed, result: ' . $e->getMessage(), 'error');
		}

		$this->redirect('Meeting:listing');
	}

	/**
	 * @param  integer $id
	 * @return void
	 */
	public function actionUpdate($id)
	{
		try {
			$data = $this->getHttpRequest()->getPost();
			$result = $this->getModel()->update($id, $data);

			Debugger::log('Modification of meeting id ' . $id . ' with data ' . json_encode($data) . ' successfull, result: ' . json_encode($result), Debugger::INFO);
			$this->flashMessage('Položka byla úspěšně upravena', 'ok');
		} catch(Exception $e) {
			Debugger::log('Modification of meeting id ' . $id . ' failed, result: ' . $e->getMessage(), Debugger::ERROR);
			$this->flashMessage('Modification of meeting id ' . $id . ' failed, result: ' . $e->getMessage(), 'error');
		}

		$this->redirect('Meeting:listing');
	}

	/**
	 * Delete item
	 * @param  int $id of item
	 * @return void
	 */
	public function actionDelete($id)
	{
		try {
			$result = $this->getModel()->delete($id);
			Debugger::log('Destroying of meeting('. $id .') successfull, result: ' . json_encode($result), Debugger::INFO);
			$this->flashMessage('Položka byla úspěšně smazána', 'ok');
		} catch(Exception $e) {
			Debugger::log('Destroying of meeting('. $id .') failed, result: ' .  $e->getMessage(), Debugger::ERROR);
			$this->flashMessage('Destroying of meeting failed, result: ' . $e->getMessage(), 'error');
		}

		$this->redirect('Meeting:listing');
	}

	/**
	 * @return void
	 */
	public function renderNew()
	{
		$template = $this->getTemplate();
		////inicializace promenych
		$template->error_start = "";
		$template->error_end = "";
		$template->error_open_reg = "";
		$template->error_close_reg = "";
		$template->error_login = "";
	}

	/**
	 * @return void
	 */
	public function renderEdit($id)
	{
		$template = $this->getTemplate();
		////inicializace promenych
		$template->error_start = "";
		$template->error_end = "";
		$template->error_open_reg = "";
		$template->error_close_reg = "";
		$template->error_login = "";

		$template->meetingId = $id;
		$template->data = $this->getModel()->find($id);
	}

	/**
	 * Render entire page
	 * @return void
	 */
	public function renderListing()
	{
		$template = $this->getTemplate();
		////inicializace promenych
		$template->error_start = "";
		$template->error_end = "";
		$template->error_open_reg = "";
		$template->error_close_reg = "";
		$template->error_login = "";

		$template->meetingId = $this->getMeetingId();
		$template->render = $this->getModel()->all();
		$template->data = $this->getModel()->find($this->getMeetingId());
	}

}
