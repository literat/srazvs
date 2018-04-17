<?php

namespace App\Presenters;

use App\Models\CategoryModel;

class CategoryPresenter extends BasePresenter
{
	/**
	 * @param CategoryModel $model
	 */
	public function __construct(CategoryModel $model)
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
	 * @param  int                               $id
	 * @return void
	 * @throws \Nette\Application\AbortException
	 */
	public function actionDelete($id)
	{
		try {
			$result = $this->getModel()->delete($id);
			$this->logInfo('Destroying of category successfull, result: %s', json_encode($result));
			$this->flashMessage('Položka byla úspěšně smazána', 'ok');
		} catch (\Exception $e) {
			$this->logError('Destroying of category failed, result: %s', $e->getMessage());
			$this->flashMessage('Destroying of category failed, result: ' . $e->getMessage(), 'error');
		}

		$this->redirect('Category:listing');
	}

	public function actionCreate()
	{
		try {
			$data = $this->getHttpRequest()->getPost();
			$result = $this->getModel()->create($data);

			$this->logInfo('Creation of category successfull, result: %s', json_encode($result));

			$this->flashMessage('Položka byla úspěšně vytvořena', 'ok');
		} catch (\Exception $e) {
			$this->logError(
				'Creation of category with data %s failed, result: %s',
				[
					json_encode($data),
					$e->getMessage()
				]
			);

			$this->flashMessage('Creation of category failed, result: ' . $e->getMessage(), 'error');
		}

		$this->redirect('Category:listing');
	}

	/**
	 * @param  integer                           $id
	 * @return void
	 * @throws \Nette\Application\AbortException
	 */
	public function actionUpdate($id)
	{
		try {
			$data = $this->getHttpRequest()->getPost();
			$result = $this->getModel()->update($id, $data);

			$this->logInfo(
				'Modification of category id %s with data %s successfull, result: %s',
				[
					$id,
					json_encode($data),
					json_encode($result),
				]
			);
			$this->flashMessage('Položka byla úspěšně upravena', 'ok');
		} catch (\Exception $e) {
			$this->logError(
				'Modification of category id %s failed, result: %s',
				[
					$id,
					$e->getMessage(),
				]
			);

			$this->flashMessage('Modification of category id ' . $id . ' failed, result: ' . $e->getMessage(), 'error');
		}

		$this->redirect('Category:listing');
	}

	public function renderListing()
	{
		$model = $this->getModel();
		$template = $this->getTemplate();

		$template->categories = $model->all();
		$template->mid = $this->meetingId;
		$template->heading = $this->heading;
	}

	public function renderNew()
	{
		$template = $this->getTemplate();

		$template->mid = $this->meetingId;
		$template->heading = 'nová kategorie';
	}

	public function renderEdit($id)
	{
		$model = $this->getModel();
		$template = $this->getTemplate();

		$template->heading = 'úprava kategorie';
		$template->mid = $this->meetingId;
		$template->id = $id;
		$template->category = $model->find($id);
	}
}
