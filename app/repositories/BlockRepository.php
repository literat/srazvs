<?php

namespace App\Repositories;

use App\Models\BlockModel;
use Nette\Database\Table\ActiveRow;

class BlockRepository
{

	/**
	 * @var BlockModel
	 */
	protected $blockModel;

	/**
	 * @param BlockModel $blockModel
	 */
	public function __construct(
		BlockModel $blockModel
	) {
		$this->setBlockModel($blockModel);
	}

	/**
	 * @param  int $meetingId
     * @return self
	 */
	public function setMeetingId(int $meetingId): self
	{
		$this->getBlockModel()->setMeetingId($meetingId);

		return $this;
	}

	/**
	 * @return array
	 */
	public function all(): array
	{
		return $this->getBlockModel()->all();
	}

	/**
	 * @param  int $id
	 * @return \Nette\Database\Table\ActiveRow
	 */
	public function find(int $id): ActiveRow
	{
		return $this->getBlockModel()->find($id);
	}

	/**
	 * @param  string $meetingId
	 * @return array
	 */
	public function findByMeeting(string $meetingId): array
	{
		return $this->getBlockModel()->findBymeeting($meetingId);
	}

	/**
	 * @param  $block
	 * @return \Nette\Database\Table\ActiveRow
	 */
	public function create($block): ActiveRow
	{
		unset($block->id);
		return $this->getBlockModel()->create((array) $block);
	}

	/**
	 * @param $id
	 * @return bool
	 */
	public function delete($id): bool
	{
		return $this->getBlockModel()->delete($id);
	}

	/**
	 * @return BlockModel
	 */
	protected function getBlockModel()
	{
		return $this->blockModel;
	}

	/**
	 * @param  BlockModel $model
	 * @return $this
	 */
	protected function setBlockModel(BlockModel $model): self
	{
		$this->blockModel = $model;

		return $this;
	}

}
