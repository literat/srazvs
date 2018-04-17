<?php

namespace App\Presenters;

use App\Components\Forms\BlockForm;
use App\Components\Forms\Factories\IBlockFormFactory;
use App\Repositories\BlockRepository;
use App\Services\Emailer;
use Nette\Utils\ArrayHash;

class BlockPresenter extends BasePresenter
{
	const REDIRECT_DEFAULT = 'Block:listing';

	/**
	 * @var Emailer
	 */
	private $emailer;

	/**
	 * @var BlockRepository
	 */
	private $blockRepository;

	/**
	 * @var IBlockFormFactory
	 */
	private $blockFormFactory;

	/**
	 * @var int
	 */
	protected $blockId;

	/**
	 * @param BlockRepository $blockRepository
	 * @param Emailer         $emailer
	 */
	public function __construct(BlockRepository $blockRepository, Emailer $emailer)
	{
		$this->setBlockRepository($blockRepository);
		$this->setEmailer($emailer);
	}

	/**
	 * @param IBlockFormFactory $factory
	 */
	public function injectBlockFormFactory(IBlockFormFactory $factory)
	{
		$this->blockFormFactory = $factory;
	}

	public function startup()
	{
		parent::startup();
		$this->allowAdminAccessOnly();
		$meetingId = $this->getMeetingId();
		$this->getBlockRepository()->setMeetingId($meetingId);
	}

	public function actionCreate(ArrayHash $block)
	{
		try {
			$this->logInfo('Storing new block.');

			$result = $this->getBlockRepository()->create($block);

			$this->logInfo(
				'Creation of block successfull, result: %s',
				[
					json_encode($result)
				]
			);
			$this->flashSuccess('Položka byla úspěšně vytvořena');
		} catch (\Exception $e) {
			$this->logError(
				'Creation of block with data %s failed, result: %s',
				[
					json_encode($block),
					$e->getMessage(),
				]
			);
			$this->flashFailure('Creation of block failed, result: ' . $e->getMessage());
			$result = false;
		}

		return $result;
	}

	/**
	 * @param  integer   $id
	 * @param  ArrayHash $block
	 * @return boolean
	 */
	public function actionUpdate($id, ArrayHash $block)
	{
		try {
			$this->logInfo('Updating block.');

			$result = $this->getBlockRepository()->update($id, $block);

			$this->logInfo(
				'Modification of block id %s with data =s successfull, result: %s',
				[
					$id,
					json_encode($block),
					json_encode($result),
				]
			);
			$this->flashSuccess('Položka byla úspěšně uložena.');
		} catch (\Exception $e) {
			$this->logError('Záznam se nepodařilo uložit.');
			$this->flashFailure('Modification of block id ' . $id . ' failed, result: ' . $e->getMessage());
			$result = false;
		}

		return $result;
	}

	/**
	 * @param  int                               $id
	 * @return void
	 * @throws \Nette\Application\AbortException
	 */
	public function actionDelete($id)
	{
		try {
			$result = $this->getBlockRepository()->delete($id);
			$this->logInfo('Destroying of block successfull, result: ' . json_encode($result));
			$this->flashSuccess('Položka byla úspěšně smazána.');
		} catch (\Exception $e) {
			$this->logError('Destroying of block failed, result: ' . $e->getMessage());
			$this->flashFailure('Destroying of block failed, result: ' . $e->getMessage());
		}

		$this->redirect(self::REDIRECT_DEFAULT);
	}

	/**
	 * Send mail to tutor.
	 *
	 * @param $id
	 * @return void
	 * @throws \Nette\Application\AbortException
	 */
	public function actionMail($id)
	{
		try {
			$tutors = $this->getBlockRepository()->findTutor($id);
			$recipients = $this->parseTutorEmail($tutors);

			$this->getEmailer()->tutor($recipients, $tutors->guid, 'block');

			$this->logInfo(
				'Sending email to block tutor successfull, result: %s, %s',
				[
					json_encode($recipients),
					$tutors->guid,
				]
			);
			$this->flashSuccess('Email lektorovi byl odeslán..');
		} catch (\Exception $e) {
			$this->logError('Sending email to block tutor failed, result: %s', [$e->getMessage()]);
			$this->flashFailure('Email lektorovi nebyl odeslán, result: ' . $e->getMessage());
		}

		$this->redirect('Block:edit', $id);
	}

	public function renderNew()
	{
		$template = $this->getTemplate();
		$template->heading = 'nový blok';
	}

	/**
	 * Prepare data for editing.
	 *
	 * @param  int  $id of Block
	 * @return void
	 */
	public function renderEdit($id)
	{
		$template = $this->getTemplate();

		$template->heading = 'úprava bloku';

		$this->blockId = $id;
		$block = $this->getBlockRepository()->find($id);
		$template->block = $block;
		$template->id = $id;

		$block->start_hour = (int) $block->from->format('%H');
		$block->start_minute = (int) $block->from->format('%I');
		$block->end_hour = (int) $block->to->format('%H');
		$block->end_minute = (int) $block->to->format('%I');

		$this['blockForm']->setDefaults($block);
	}

	public function renderListing()
	{
		$template = $this->getTemplate();
		$template->blocks = $this->getBlockRepository()->all();
		$template->mid = $this->meetingId;
		$template->heading = $this->heading;
	}

	protected function createComponentBlockForm(): BlockForm
	{
		$control = $this->blockFormFactory->create();
		$control->setMeetingId($this->getMeetingId());
		$control->onBlockSave[] = function ($block) {
			//$guid = $this->getParameter('guid');
			$id = $this->getParameter('id');

			$this->setBacklinkFromArray($block);

			if ($id) {
				$this->actionUpdate($id, $block);
			} else {
				$this->actionCreate($block);
			}

			$this->redirect($this->getBacklink() ?: self::REDIRECT_DEFAULT);
		};

		$control->onBlockReset[] = function ($block) {
			$this->setBacklinkFromArray($block);

			$this->redirect($this->getBacklink() ?: self::REDIRECT_DEFAULT);
		};

		return $control;
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

	private function getBlockRepository(): BlockRepository
	{
		return $this->blockRepository;
	}

	private function setBlockRepository(BlockRepository $blockRepository): self
	{
		$this->blockRepository = $blockRepository;

		return $this;
	}
}
