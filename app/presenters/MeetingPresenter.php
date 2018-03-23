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
			$data = $this->getHttpRequest()->getPost();
			$result = $this->getModel()->create($data);

			$this->logInfo('Creation of meeting successfull, result: %s', json_encode($result));
			$this->flashSuccess('Položka byla úspěšně vytvořena');
		} catch(Exception $e) {
			$this->logError('Creation of meeting with data %s failed, result: %s', [
			    json_encode($data),
                $e->getMessage()
            ]);
			$this->flashFailure('Creation of meeting failed, result: %s', $e->getMessage());
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

            $this->logInfo('Modification of meeting id %s with data %s successfull, result: %s', [
                $id,
                json_encode($data),
                json_encode($result)
            ]);
			$this->flashSuccess('Položka byla úspěšně upravena');
		} catch(Exception $e) {
			$this->logError('Modification of meeting id %s failed, result: %s', [
			    $id,
			    $e->getMessage(),
            ]);
			$this->flashFailure(sprintf('Modification of meeting id %s failed, result: %s',
			    $id,
                $e->getMessage()
            ));
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

			$this->logInfo('Destroying of meeting(%s) successfull, result: %s', [
			    $id,
			    json_encode($result),
            ]);
			$this->flashSuccess('Položka byla úspěšně smazána');
		} catch(Exception $e) {
			$this->logError('Destroying of meeting(%s) failed, result: %s', [
			    $id,
			    $e->getMessage(),
            ]);
			$this->flashFailure(sprintf('Destroying of meeting failed, result: %s', $e->getMessage()));
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
