<?php

namespace App\Repositories;

use App\Models\BlockModel;

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
	 * @param  string $meetingId
	 * @return array
	 */
	public function findByMeeting(string $meetingId): array
	{
		return $this->getBlockModel()->findBymeeting($meetingId);
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
