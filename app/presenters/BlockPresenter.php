<?php

namespace App\Presenters;

use App\Services\Emailer;
use App\Repositories\BlockRepository;
use App\Components\Forms\Factories\IBlockFormFactory;
use App\Components\Forms\BlockForm;
use \Exception;
use Nette\Utils\ArrayHash;

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
	public function __construct(BlockRepository $blockRepository, Emailer $emailer)
	{
		$this->setBlockRepository($blockRepository);
		$this->setEmailer($emailer);
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
	public function actionCreate(ArrayHash $block)
	{
		try {
			$block = $this->transformBlock($block);

			$this->logInfo('Storing new block.');

			$this->guardToGreaterThanFrom($block->from, $block->to);
			$result = $this->getBlockRepository()->create($block);

			$this->logInfo('Creation of block successfull, result: ' . json_encode($result));
			$this->flashSuccess('Položka byla úspěšně vytvořena');
		} catch(Exception $e) {
			$this->logError('Creation of block with data ' . json_encode($block) . ' failed, result: ' . $e->getMessage());
			$this->flashFailure('Creation of block failed, result: ' . $e->getMessage());
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
			$block = $this->transformBlock($block);

			$this->guardToGreaterThanFrom($block['from'], $block['to']);
			$result = $this->getBlockRepository()->update($id, $block);

			$this->logInfo('Modification of block id ' . $id . ' with data ' . json_encode($block) . ' successfull, result: ' . json_encode($result));
			$this->flashSuccess('Položka byla úspěšně upravena.');
		} catch(Exception $e) {
			$this->logError('Modification of block id ' . $id . ' failed, result: ' . $e->getMessage());
			$this->flashFailure('Modification of block id ' . $id . ' failed, result: ' . $e->getMessage());
		}

		return $result;
	}

    /**
     * @param  int $id
     * @return void
     * @throws \Nette\Application\AbortException
     */
	public function actionDelete($id)
	{
		try {
			$result = $this->getBlockRepository()->delete($id);
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

		$this->blockId = $id;
		$block = $this->getBlockRepository()->find($id);
		$block = ArrayHash::from($block);
		$template->block = $block;
		$template->id = $id;

		$block->start_hour = (int) $block->from->format('%H');
		$block->start_minute = (int) $block->from->format('%I');
		$block->end_hour = (int) $block->to->format('%H');
		$block->end_minute = (int) $block->to->format('%I');

		$this['blockForm']->setDefaults($block);
	}

	/**
	 * @return void
	 */
	public function renderListing()
	{
		$template = $this->getTemplate();
		$template->blocks = $this->getBlockRepository()->all();
		$template->mid = $this->meetingId;
		$template->heading = $this->heading;
	}

    /**
     * @param  ArrayHash $block
     * @return ArrayHash
     */
	protected function transformBlock(ArrayHash $block): ArrayHash
    {
        $block->from = date('H:i:s', mktime($block->start_hour, $block->start_minute, 0, 0, 0, 0));
        $block->to = date('H:i:s', mktime($block->end_hour, $block->end_minute, 0, 0, 0, 0));
        $block->meeting = $this->getMeetingId();
        $block->program = strval($block->program) ?: '0';
        $block->display_progs = strval($block->display_progs) ?: '0';

        unset($block->start_hour);
        unset($block->end_hour);
        unset($block->start_minute);
        unset($block->end_minute);
        unset($block->backlink);

        return $block;
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
	protected function getEmailer(): Emailer
	{
		return $this->emailer;
	}

	/**
	 * @param  Emailer $emailer
	 * @return self
	 */
	protected function setEmailer(Emailer $emailer): self
	{
		$this->emailer = $emailer;
		return $this;
	}

	/**
	 * @param  date $from
	 * @param  date $to
	 * @return void
     * @throws Exception
	 */
	private function guardToGreaterThanFrom($from, $to)
	{
		if($from > $to) {
			throw new Exception('Starting time is greater then finishing time.');
		}
	}

	/**
	 * @return BlockRepository
	 */
	private function getBlockRepository(): BlockRepository
	{
		return $this->blockRepository;
	}

	/**
	 * @param  BlockRepository $blockRepository
     * @return self
	 */
	private function setBlockRepository(BlockRepository $blockRepository): self
	{
		$this->blockRepository = $blockRepository;

		return $this;
	}



}
