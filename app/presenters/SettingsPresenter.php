<?php

namespace App\Presenters;

use App\Models\SettingsModel;
use App\Services\Emailer;
use Tracy\Debugger;

class SettingsPresenter extends BasePresenter
{

	/**
	 * @var Emailer
	 */
	private $emailer;

	/**
	 * @param SettingsModel $settingsModel
	 * @param Emailer       $emailer
	 */
	public function __construct(SettingsModel $settingsModel, Emailer $emailer)
	{
		$this->setModel($settingsModel);
		$this->setEmailer($emailer);
	}

	/**
	 * @param 	string 	$id
	 * @return 	void
	 */
	public function actionUpdate($id)
	{
		try {
			$data = $this->getHttpRequest()->getPost();
			$this->getModel()->modifyMailJSON($id, $data['subject'], $data['message']);

			Debugger::log('Settings: mail type ' . $id . ' update succesfull.', Debugger::INFO);
			$this->flashMessage('Settings: mail type ' . $id . ' update succesfull.', 'ok');
		} catch(Exception $e) {
			Debugger::log('Settings: mail type ' . $id . ' update failed, result: ' . $e->getMessage(), Debugger::ERROR);
			$this->flashMessage('Settings: mail type ' . $id . ' update failed, result: ' . $e->getMessage(), 'error');
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

			Debugger::log('Settings: debug regime update succesfull.', Debugger::INFO);
			$this->flashMessage('Settings: debug regime update succesfull.', 'ok');
		} catch(Exception $e) {
			Debugger::log('Settings: debug update update failed, result: ' . $e->getMessage(), Debugger::ERROR);
			$this->flashMessage('Settings: debug regime update failed, result: ' . $e->getMessage(), 'error');
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
			$jsonMail = $this->getModel()->getMailJSON($id);

			$this->getEmailer()
				->sendMail(
					[$recipient => ''],
					$jsonMail->subject,
					html_entity_decode($jsonMail->message)
				);

			Debugger::log('Settings: mail type ' . $id . ' succesfully send to recipient ' . $recipient, Debugger::INFO);
			$this->flashMessage('Settings: mail type ' . $id . ' succesfully send.', 'ok');
		} catch (Exception $e) {
			Debugger::log('Settings: mail type ' . $id . ' send to recipient ' . $recipient . ' failed, result: ' . $e->getMessage(), Debugger::ERROR);
			$this->flashMessage('Settings: mail type ' . $id . ' send to recipient failed, result: ' . $e->getMessage(), 'error');
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
		$error = '';

		$template->payment_subject = $settingsModel->getMailJSON('cost')->subject;
		$template->payment_message = $settingsModel->getMailJSON('cost')->message;
		$template->payment_html_message = html_entity_decode($settingsModel->getMailJSON('cost')->message);
		$template->advance_subject = $settingsModel->getMailJSON('advance')->subject;
		$template->advance_message = $settingsModel->getMailJSON('advance')->message;
		$template->advance_html_message = html_entity_decode($settingsModel->getMailJSON('advance')->message);
		$template->tutor_subject = $settingsModel->getMailJSON('tutor')->subject;
		$template->tutor_message = $settingsModel->getMailJSON('tutor')->message;
		$template->tutor_html_message = html_entity_decode($settingsModel->getMailJSON('tutor')->message);
		$template->reg_subject = $settingsModel->getMailJSON('post_reg')->subject;
		$template->reg_message = $settingsModel->getMailJSON('post_reg')->message;
		$template->reg_html_message = html_entity_decode($settingsModel->getMailJSON('post_reg')->message);
		$template->debugRegime = $settingsModel->findDebugRegime();
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

}
