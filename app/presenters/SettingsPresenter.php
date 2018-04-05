<?php

namespace App\Presenters;

use App\Models\SettingsModel;
use App\Services\Emailer;

class SettingsPresenter extends BasePresenter
{

	/**
	 * @var Emailer
	 */
	private $emailer;

	public function __construct(SettingsModel $settingsModel, Emailer $emailer)
	{
		$this->setModel($settingsModel);
		$this->setEmailer($emailer);
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
	 * @param 	string 	$id
	 * @return 	void
	 */
	public function actionUpdate($id)
	{
		try {
			$data = $this->getHttpRequest()->getPost();
			$this->getModel()->modifyMailJson($id, $data['subject'], $data['message']);

			$this->logInfo('Settings: mail type %s update succesfull.', [$id]);
			$this->flashSuccess('Settings: mail type ' . $id . ' update succesfull.');
		} catch(\Exception $e) {
			$this->logError('Settings: mail type %s update failed, result: %s', [
				$id,
				$e->getMessage(),
			]);
			$this->flashFailure('Settings: mail type ' . $id . ' update failed, result: ' . $e->getMessage());
		}

		$this->redirect('Settings:listing');
	}

	/**
	 * @return 	void
	 */
	public function actionDebug()
	{
		try {
			$activate = false;
			$data = $this->getHttpRequest()->getPost();

			if(array_key_exists('debug', $data)) {
				$activate = true;
			}

			$this->getModel()->updateDebugRegime($activate);

			$this->logInfo('Settings: debug regime update succesfull.');
			$this->flashSuccess('Settings: debug regime update succesfull.');
		} catch(\Exception $e) {
			$this->logError('Settings: debug update update failed, result: %s', [$e->getMessage()]);
			$this->flashFailure('Settings: debug regime update failed, result: ' . $e->getMessage());
		}

		$this->redirect('Settings:listing');
	}

	/**
	 * @param 	string 	$id
	 * @return 	void
	 */
	public function actionMail($id)
	{
		try {
			$recipient = $this->getHttpRequest()->getPost()['test-mail'];
			$jsonMail = $this->getModel()->getMailJson($id);

			$this->getEmailer()
				->sendMail(
					[$recipient => ''],
					$jsonMail->subject,
					html_entity_decode($jsonMail->message)
				);

			$this->logInfo('Settings: mail type ' . $id . ' succesfully send to recipient %s', [
				$id,
				$recipient,
			]);
			$this->flashSuccess('Settings: mail type ' . $id . ' succesfully send.');
		} catch (\Exception $e) {
			$this->logError('Settings: mail type %s send to recipient %s failed, result: %s', [
				$id,
				$recipient,
				$e->getMessage(),
			]);
			$this->flashFailure('Settings: mail type ' . $id . ' send to recipient failed, result: ' . $e->getMessage());
		}

		$this->redirect('Settings:listing');
	}

	/**
	 * @return void
	 */
	public function renderListing()
	{
		$settingsModel = $this->getModel();
		$template = $this->getTemplate();

		$template->payment_subject = $settingsModel->getMailJson('cost')->subject;
		$template->payment_message = $settingsModel->getMailJson('cost')->message;
		$template->payment_html_message = html_entity_decode($settingsModel->getMailJson('cost')->message);
		$template->advance_subject = $settingsModel->getMailJson('advance')->subject;
		$template->advance_message = $settingsModel->getMailJson('advance')->message;
		$template->advance_html_message = html_entity_decode($settingsModel->getMailJson('advance')->message);
		$template->tutor_subject = $settingsModel->getMailJson('tutor')->subject;
		$template->tutor_message = $settingsModel->getMailJson('tutor')->message;
		$template->tutor_html_message = html_entity_decode($settingsModel->getMailJson('tutor')->message);
		$template->reg_subject = $settingsModel->getMailJson('post_reg')->subject;
		$template->reg_message = $settingsModel->getMailJson('post_reg')->message;
		$template->reg_html_message = html_entity_decode($settingsModel->getMailJson('post_reg')->message);
		$template->debugRegime = $settingsModel->findDebugRegime();
	}

	protected function getEmailer(): Emailer
	{
		return $this->emailer;
	}

	protected function setEmailer(Emailer $emailer): self
	{
		$this->emailer = $emailer;

		return $this;
	}

}
